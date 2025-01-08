<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="RoomInventory",
 *     type="object",
 *     description="房間庫存結構",
 *     @OA\Property(property="id", type="integer", description="房間庫存ID", example=1),
 *     @OA\Property(property="room_type_id", type="integer", description="房間類型ID", example=1),
 *     @OA\Property(property="room_type", ref="#/components/schemas/RoomType"),
 *     @OA\Property(property="date", type="string", format="date", description="庫存日期", example="2024-01-01"),
 *     @OA\Property(property="total_rooms", type="integer", description="總房間數量", example=50),
 *     @OA\Property(property="booked_rooms", type="integer", description="已預訂房間數量", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="建立時間", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新時間", example="2024-01-10T12:00:00Z")
 * )
 */
class RoomInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'date',
        'total_rooms',
        'booked_rooms',
    ];
    
    protected $casts = [
        'date' => 'datetime:Y-m-d',  
    ];

    protected $appends = ['can_book'];

    /**
     * 判斷是否可以訂房
     */
    protected function canBook(): Attribute
    {
        return new Attribute(
            get: fn () => $this->total_rooms > $this->booked_rooms,
        );
    }
    
}
