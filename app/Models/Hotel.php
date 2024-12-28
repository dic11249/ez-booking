<?php

namespace App\Models;

use App\Models\Amenity;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Hotel",
 *     type="object",
 *     description="飯店模型結構",
 *     @OA\Property(property="id", type="integer", description="飯店ID", example=1),
 *     @OA\Property(property="name", type="string", description="飯店名稱", example="Hotel Paradise"),
 *     @OA\Property(property="description", type="string", description="飯店描述", example="A luxury hotel near the beach"),
 *     @OA\Property(property="address", type="string", description="飯店地址", example="123 Paradise Road"),
 *     @OA\Property(property="city", type="string", description="所在城市", example="Los Angeles"),
 *     @OA\Property(property="country", type="string", description="所在國家", example="USA"),
 *     @OA\Property(property="latitude", type="number", format="float", description="緯度", example=34.0522),
 *     @OA\Property(property="longitude", type="number", format="float", description="經度", example=-118.2437),
 *     @OA\Property(property="total_rooms", type="integer", description="總房間數", example=120),
 *     @OA\Property(property="rating", type="number", format="float", description="評分", example=4.5),
 *     @OA\Property(property="is_active", type="boolean", description="是否啟用", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="建立時間", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新時間", example="2024-01-10T12:00:00Z")
 * )
 */
class Hotel extends Model
{
    /** @use HasFactory<\Database\Factories\HotelFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'total_rooms',
        'rating',
        'is_active',
    ];

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'hotel_amenities');
    }
}
