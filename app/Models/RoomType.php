<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="RoomType",
 *     description="房間類型模型",
 *     @OA\Property(property="id", type="integer", description="房間類型ID"),
 *     @OA\Property(property="hotel_id", type="integer", description="所屬旅館ID"),
 *     @OA\Property(property="name", type="string", description="房間類型名稱"),
 *     @OA\Property(property="capacity", type="integer", description="房間容納人數"),
 *     @OA\Property(property="base_price", type="number", description="基本價格"),
 *     @OA\Property(property="description", type="string", description="房間類型描述"),
 *     @OA\Property(
 *         property="amenities", 
 *         type="object", 
 *         description="房間設施",
 *         @OA\Property(property="wifi", type="boolean"),
 *         @OA\Property(property="airConditioner", type="boolean")
 *     )
 * )
 * 
 */
class RoomType extends Model
{
    /** @use HasFactory<\Database\Factories\RoomTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'capacity',
        'base_price',
        'description',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
