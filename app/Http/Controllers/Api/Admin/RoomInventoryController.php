<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomInventory;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomInventoryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/room-types/{roomType}/inventories",
     *     summary="設定房間庫存",
     *     description="此 API 用於設定房間庫存數量，若該日期與房型的庫存已存在，則更新庫存數量。",
     *     operationId="storeRoomInventory",
     *     tags={"AdminRoomInventory"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="設定房間庫存的資料",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"room_type_id", "date", "total_rooms"},
     *                 @OA\Property(property="date", type="string", format="date", description="日期", example="2024-12-31"),
     *                 @OA\Property(property="total_rooms", type="integer", description="總房間數", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功設定或更新房間庫存",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="庫存 ID", example=1),
     *                 @OA\Property(property="room_type_id", type="integer", description="房型 ID", example=1),
     *                 @OA\Property(property="date", type="string", format="date", description="日期", example="2024-12-31"),
     *                 @OA\Property(property="total_rooms", type="integer", description="總房間數", example=100),
     *                 @OA\Property(property="booked_rooms", type="integer", description="已預訂的房間數", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="建立時間", example="2024-12-31T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="更新時間", example="2024-12-31T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function setInventory(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date|after_or_equal:today',
            'total_rooms' => 'required|integer|min:0',
        ]);

        $inventory = RoomInventory::updateOrCreate(
            [
                'room_type_id' => $request->room_type_id,
                'date' => $request->date,
            ],
            [
                'total_rooms' => $request->total_rooms,
                'booked_rooms' => 0,
            ]
        );

        return response()->json($inventory, 201);
    }
}
