<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/hotels",
     *     summary="取得所有飯店清單",
     *     description="回傳所有飯店的基本資訊，支援分頁格式",
     *     tags={"Hotel"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="分頁頁數",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Hotel")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Hotel::query();

        // 加入條件：僅查詢 `is_active = true`
        $query->where('is_active', true);

        // 可選條件
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating); // 篩選評分大於等於指定值
        }

        // 分頁查詢
        $hotels = $query->paginate(10);
        
        return response()->json($hotels);
    }

    /**
     * @OA\Get(
     *     path="/api/hotels/{id}",
     *     summary="取得指定飯店資訊",
     *     description="回傳特定飯店的詳細資訊",
     *     tags={"Hotel"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="飯店ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(ref="#/components/schemas/Hotel")
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function show($id)
    {
        $hotel = Hotel::findOrFail($id);
        return response()->json($hotel);
    }
}
