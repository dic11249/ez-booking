<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AmenityType;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class AmenityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/amenities",
     *     summary="取得所有設施",
     *     description="回傳設施的基本資訊，支援分頁格式",
     *     tags={"AdminAmenity"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Amenity")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $amenities = Amenity::paginate();

        return response()->json($amenities, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/amenities",
     *     summary="新增設施",
     *     description="建立一個新的設施",
     *     tags={"AdminAmenity"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type"},
     *             @OA\Property(property="name", type="string", example="Wi-Fi"),
     *             @OA\Property(property="type", type="string", example="Basic"),
     *             @OA\Property(property="description", type="string", example="Wi-Fi"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="成功",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(response=422, description="驗證失敗")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::enum(AmenityType::class)],
            'description' => 'nullable|string',
        ]);
        
        $amenity = Amenity::create($validated);

        return response()->json($amenity, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/amenities/{id}",
     *     summary="取得指定設施資訊",
     *     description="回傳特定設施的詳細資訊",
     *     tags={"AdminAmenity"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="設施ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function show(Amenity $amenity)
    {
        return response()->json($amenity, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/amenities/{id}",
     *     summary="更新指定設施資訊",
     *     description="更新特定設施的詳細資訊",
     *     tags={"AdminAmenity"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="設施ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => ['required', Rule::enum(AmenityType::class)],
            'description' => 'nullable|string',
        ]);

        $amenity->update($validated);

        return response()->json($amenity, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/amenities/{id}",
     *     summary="刪除指定設施",
     *     description="刪除特定設施",
     *     tags={"AdminAmenity"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="設施ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="刪除成功"
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(null, 204);
    }
}
