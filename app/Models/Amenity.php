<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Amenity",
 *     type="object",
 *     description="設施結構",
 *     @OA\Property(property="id", type="integer", description="設施ID", example=1),
 *     @OA\Property(property="name", type="string", description="設施名稱", example="Wi-Fi"),
 *     @OA\Property(property="type", type="string", description="設施類別", example="Basic"),
 *     @OA\Property(property="description", type="string", description="設施描述", example="Wi-Fi"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="建立時間", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新時間", example="2024-01-10T12:00:00Z")
 * )
 */
class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string', // ENUM 會被存儲為字串
    ];
}
