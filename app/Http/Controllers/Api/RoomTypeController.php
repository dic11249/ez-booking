<?php

namespace App\Http\Controllers\Api;

use App\Models\RoomType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomTypeController extends Controller
{
    /**
     * 取得特定旅館的所有房間類型
     *
     * @OA\Get(
     *     path="/api/hotels/{hotelId}/room-types",
     *     summary="列出特定旅館的所有房間類型",
     *     tags={"RoomTypes"},
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

        return response()->json($roomTypes, 201);
    }

    /**
     * 取得特定房間類型詳細資訊
     *
     * @OA\Get(
     *     path="/api/hotels/{hotelId}/room-types/{roomTypeId}",
     *     summary="取得特定房間類型詳細資訊",
     *     tags={"RoomTypes"},
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
    public function show(int $hotelId, int $roomTypeId)
    {
        $roomType = RoomType::where('hotel_id', $hotelId)->findOrFail($roomTypeId);

        return response()->json($roomType, 200);
    }
}
