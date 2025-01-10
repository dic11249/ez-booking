<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Booking",
 *     required={"user_id", "room_type_id", "start_date", "end_date", "total_price", "status", "guest_count"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, description="Booking unique identifier"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="ID of the user making the booking"),
 *     @OA\Property(property="room_type_id", type="integer", format="int64", description="ID of the booked room type"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Check-in date"),
 *     @OA\Property(property="end_date", type="string", format="date", description="Check-out date"),
 *     @OA\Property(property="total_price", type="number", format="float", description="Total price of the booking"),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "confirmed", "cancelled", "completed"},
 *         description="Current status of the booking"
 *     ),
 *     @OA\Property(property="guest_count", type="integer", description="Number of guests"),
 *     @OA\Property(property="special_requests", type="string", nullable=true, description="Special requests or notes"),
 *     @OA\Property(property="created_at", type="string", format="datetime", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="datetime", readOnly=true)
 * )
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_type_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'guest_count',
        'special_requests'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'guest_count' => 'integer'
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
