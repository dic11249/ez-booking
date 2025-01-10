<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RoomInventory;
use App\Models\RoomType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /** 檢查房間是否可以預訂 */
    protected function checkAvailability(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date)->subDay(); // 減去一天，因為退房日不計算

        // 檢查房型是否存在
        $roomType = RoomType::findOrFail($request->room_type_id);

        // 一次性取得指定日期範圍內的庫存
        $inventories = RoomInventory::where('room_type_id', $request->room_type_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
            })
            ->get();
        
        // 如果庫存記錄數量不等於住宿天數，表示有缺少的日期
        $expectedDays = (int)$startDate->diffInDays($endDate) + 1;
        if ($inventories->count() !== $expectedDays) {
            return false;
        }
        
        // 檢查是否有任何日期無法預訂或庫存不足
        return !$inventories->contains(function ($inventory) use ($roomType) {
            return !$inventory->can_book;
        });
    }

    /**
     * 更新庫存
     */
    private function updateInventory(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = $endDate = Carbon::parse($request->end_date)->subDay(); // 減去一天(check_out)
        $roomTypeId = $request->room_type_id;

        $inventories = RoomInventory::where('room_type_id', $roomTypeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->lockForUpdate() // 確保並發安全性
            ->get();

        foreach ($inventories as $inventory) {
            $inventory->booked_rooms += 1;
            $inventory->save();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bookings/{booking}",
     *     summary="取得特定訂單詳情",
     *     description="回傳指定訂單的詳細資訊，包含房型資訊",
     *     tags={"Bookings"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         description="訂單ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功取得訂單詳情",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="無權限查看此訂單"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="訂單不存在"
     *     )
     * )
     */
    public function index()
    {
        $bookings = Auth::guard('users')->user()->bookings;

        return response()->json($bookings, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/bookings/{booking}",
     *     summary="取得特定訂單詳情",
     *     description="回傳指定訂單的詳細資訊，包含房型資訊",
     *     tags={"Bookings"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         description="訂單ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功取得訂單詳情",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="無權限查看此訂單"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="訂單不存在"
     *     )
     * )
     */
    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::guard('users')->id()) {
            abort(403, 'Forbidden');
        }

        // 關聯出房型
        $booking->load('roomType');

        return response()->json($booking, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/room-types/{roomType}/booking",
     *     summary="新增訂單",
     *     description="根據使用者請求建立新的訂單並更新庫存",
     *     operationId="storeBooking",
     *     tags={"Bookings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1, description="使用者 ID"),
     *             @OA\Property(property="room_type_id", type="integer",  example=1, description="房間 ID"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-01-01", description="開始日期"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-01-05", description="結束日期"),
     *             @OA\Property(property="guest_count", type="integer", example=2, description="客人數量"),
     *             @OA\Property(property="special_requests", type="string", example="需要加床", description="特殊需求"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="訂單建立成功",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'guest_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $booking = DB::transaction(function () use ($request) {
            $can_booking = $this->checkAvailability($request);

            if (!$can_booking) {
                throw new Exception('房間庫存不足，無法預訂');
            }

            // 取得房型價格
            $roomType = RoomType::findOrFail($request->room_type_id);

            // 計算天數
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);

            // 計算總價
            $totalPrice = $roomType->base_price * $days;

            // 扣減庫存
            $this->updateInventory($request);

            // 建立訂單
            $booking = Booking::create([
                'user_id' => $request->user_id,
                'room_type_id' => $request->room_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'guest_count' => $request->guest_count,
                'special_requests' => $request->special_requests,
            ]);

            return $booking;
        });

        return response()->json($booking, 201);
    }
}
