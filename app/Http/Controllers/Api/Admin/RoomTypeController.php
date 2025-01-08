<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    /**
     * 取得特定旅館的所有房間類型
     *
     * @OA\Get(
     *     path="/api/admin/hotels/{hotelId}/room-types",
     *     summary="列出特定旅館的所有房間類型",
     *     tags={"AdminRoomTypes"},
     *     @OA\Parameter(
     *         name="hotelId",
     *         in="path",
     *         description="旅館ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="成功取得房間類型列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RoomType")
     *         )
     *     )
     * )
     */
    public function index(int $hotelId)
    {
        $roomTypes = RoomType::where('hotel_id', $hotelId)->get();

        return response()->json($roomTypes, 200);
    }

    /**
     * 為特定旅館建立新的房間類型
     *
     * @OA\Post(
     *     path="/api/admin/hotels/{hotelId}/room-types",
     *     summary="建立新的房間類型",
     *     tags={"AdminRoomTypes"},
     *     @OA\Parameter(
     *         name="hotelId",
     *         in="path",
     *         description="旅館ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="房型資料",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "capacity", "base_price", "description"},
     *                 @OA\Property(property="name", type="string", description="房型名稱", example="Standard Room"),
     *                 @OA\Property(property="capacity", type="integer", description="最大容納人數", example=2),
     *                 @OA\Property(property="base_price", type="number", format="float", description="基礎價格", example=100.00),
     *                 @OA\Property(property="description", type="string", description="房型描述", example="A cozy room with a double bed."),
     *                 @OA\Property(property="amenities", type="array", description="設施", @OA\Items(type="string", example="Wi-Fi"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="成功建立房間類型",
     *         @OA\JsonContent(ref="#/components/schemas/RoomType")
     *     )
     * )
     */
    public function store(Request $request, int $hotelId)
    {
        $hotel = Hotel::findOrFail($hotelId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
        ]);

        $roomType = $hotel->roomTypes()->create($validated);

        return response()->json($roomType, 201);
    }

    /**
     * 取得特定房間類型詳細資訊
     *
     * @OA\Get(
     *     path="/api/admin/hotels/{hotelId}/room-types/{roomTypeId}",
     *     summary="取得特定房間類型詳細資訊",
     *     tags={"AdminRoomTypes"},
     *     @OA\Parameter(
     *         name="hotelId",
     *         in="path",
     *         description="旅館ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="roomTypeId",
     *         in="path",
     *         description="房間類型ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功取得房間類型詳細資訊",
     *         @OA\JsonContent(ref="#/components/schemas/RoomType")
     *     )
     * )
     */
    public function show(Request $request, int $hotelId, int $roomTypeId)
    {
        // 取得 year 和 month 參數，若未提供則使用當前年份和月份
        $year = $request->input('year', now()->year); // 預設為當前年份
        $month = $request->input('month', now()->month); // 預設為當前月份

        $roomType = RoomType::with(['roomInventories' => function ($query) use ($year, $month) {
            // 只撈出指定年份與月份的房間庫存資料
            $query->whereYear('date', $year)
                ->whereMonth('date', $month);
        }])
        ->where('hotel_id', $hotelId)
        ->findOrFail($roomTypeId);

        return response()->json($roomType, 200);
    }

    /**
     * 更新特定房間類型資訊
     *
     * @OA\Put(
     *     path="/api/admin/hotels/{hotelId}/room-types/{roomTypeId}",
     *     summary="更新房間類型資訊",
     *     tags={"AdminRoomTypes"},
     *     @OA\Parameter(
     *         name="hotelId",
     *         in="path",
     *         description="旅館ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="roomTypeId",
     *         in="path",
     *         description="房間類型ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功更新房間類型",
     *         @OA\JsonContent(ref="#/components/schemas/RoomType")
     *     )
     * )
     */
    public function update(Request $request, int $hotelId, int $roomTypeId)
    {
        $roomType = RoomType::where('hotel_id', $hotelId)->findOrFail($roomTypeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
        ]);

        $roomType->update($validated);

        return response($roomType, 200);
    }

    /**
     * 刪除特定房間類型
     *
     * @OA\Delete(
     *     path="/api/admin/hotels/{hotelId}/room-types/{roomTypeId}",
     *     summary="刪除房間類型",
     *     tags={"AdminRoomTypes"},
     *     @OA\Parameter(
     *         name="hotelId",
     *         in="path",
     *         description="旅館ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="roomTypeId",
     *         in="path",
     *         description="房間類型ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="成功刪除房間類型"
     *     )
     * )
     */
    public function destroy(int $hotelId, int $roomTypeId)
    {
        $roomType = RoomType::where('hotel_id', $hotelId)->findOrFail($roomTypeId);

        $roomType->delete();

        return response()->json(null, 204);
    }
}
