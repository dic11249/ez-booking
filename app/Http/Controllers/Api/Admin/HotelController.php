<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HotelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/hotels",
     *     summary="取得所有飯店清單",
     *     description="回傳所有飯店的基本資訊，支援分頁格式",
     *     tags={"AdminHotel"},
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
     * @OA\Post(
     *     path="/api/admin/hotels",
     *     summary="新增飯店",
     *     description="建立一個新的飯店",
     *     tags={"AdminHotel"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "address", "city", "country", "total_rooms"},
     *             @OA\Property(property="name", type="string", example="Hotel Paradise"),
     *             @OA\Property(property="description", type="string", example="A luxury hotel"),
     *             @OA\Property(property="address", type="string", example="123 Paradise Road"),
     *             @OA\Property(property="city", type="string", example="Los Angeles"),
     *             @OA\Property(property="country", type="string", example="USA"),
     *             @OA\Property(property="latitude", type="number", format="float", example="34.0522"),
     *             @OA\Property(property="longitude", type="number", format="float", example="-118.2437"),
     *             @OA\Property(property="total_rooms", type="integer", example="100"),
     *             @OA\Property(property="rating", type="number", format="float", example="4.5"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="成功",
     *         @OA\JsonContent(ref="#/components/schemas/Hotel")
     *     ),
     *     @OA\Response(response=422, description="驗證失敗")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_rooms' => 'required|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'amenities' => 'array', 
            'amenities.*' => 'exists:amenities,id', 
        ]);

        $hotel = Hotel::create($validated);

        if ($request->has('amenities')) {
            $hotel->amenities()->sync($request->input('amenities'));
        }

        return response()->json($hotel->load('amenities'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/hotels/{id}",
     *     summary="取得指定飯店資訊",
     *     description="回傳特定飯店的詳細資訊",
     *     tags={"AdminHotel"},
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
        $hotel = Hotel::with(['roomTypes', 'amenities'])->findOrFail($id);
        return response()->json($hotel);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/hotels/{id}",
     *     summary="更新指定飯店資訊",
     *     description="更新特定飯店的詳細資訊",
     *     tags={"AdminHotel"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="飯店ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Hotel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(ref="#/components/schemas/Hotel")
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_rooms' => 'required|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'amenities' => 'array', 
            'amenities.*' => 'exists:amenities,id', 
        ]);

        $hotel->update($validated);

        if ($request->has('amenities')) {
            $hotel->amenities()->sync($request->input('amenities'));
        }

        return response()->json($hotel->load('amenities'), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/hotels/{id}",
     *     summary="刪除指定飯店",
     *     description="刪除特定飯店",
     *     tags={"AdminHotel"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="飯店ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="刪除成功"
     *     ),
     *     @OA\Response(response=404, description="找不到資源")
     * )
     */
    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();

        return response()->json(null, 204);
    }
}
