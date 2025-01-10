<?php

namespace Tests\Feature\Api\User;

use App\Models\Booking;
use App\Models\RoomInventory;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $roomType;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('users')->plainTextToken;
        $this->roomType = RoomType::factory()->create([
            'base_price' => 1000,
        ]);

        // 創建測試用訂單
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
        ]);

        // 創建房間庫存
        $startDate = Carbon::tomorrow();
        $endDate = Carbon::tomorrow()->addDays(5);

        $dateRange = $startDate->daysUntil($endDate);
        foreach ($dateRange as $date) {
            RoomInventory::factory()->create([
                'room_type_id' => $this->roomType->id,
                'date' => $date,
                'total_rooms' => 10,
                'booked_rooms' => 5,
            ]);
        }
    }

    /**
    * 測試使用者可以查看訂單
    */
    public function test_can_view_their_bookings()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'room_type_id',
                    'start_date',
                    'end_date',
                    'total_price',
                    'status',
                ],
            ]);
    }

    /**
     * 測試使用者可以查看特定訂單
     */
    public function test_can_show_booking()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/bookings/{$this->booking->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->booking->id,
                'user_id' => $this->user->id
            ]);
    }

    /**
     * 測試使用者可以建立訂單
     */
    public function test_can_create_booking_with_available_inventory()
    {
        $startDate = Carbon::tomorrow();
        $endDate = Carbon::tomorrow()->addDays(1);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/room-types/{$this->roomType->id}/booking", [
                'user_id' => $this->user->id,
                'room_type_id' => $this->roomType->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'guest_count' => 2,
                'special_requests' => '需要加床'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'room_type_id',
                'start_date',
                'end_date',
                'total_price',
                'status'
            ]);
        
        // 驗證庫存是否已更新
        $inventory = RoomInventory::where('room_type_id', $this->roomType->id)
            ->where('date', $startDate)
            ->first();
        
        $this->assertEquals(6, $inventory->booked_rooms);
    }
}
