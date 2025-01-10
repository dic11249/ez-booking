<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create();
        $this->token = $admin->createToken('admins')->plainTextToken;

        $roomType = RoomType::factory()->create();

        $this->booking = Booking::factory()->create([
            'room_type_id' => $roomType->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

    }

    /**
     * 測試取得訂單清單
     */
    public function test_can_get_all_bookings(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/admin/bookings');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * 測試取得訂單清單根據status
     */
    public function test_can_filter_bookings_by_status(): void
    {
        Booking::factory()->create(['status' => 'confirmed']);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/admin/bookings?status=confirmed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'confirmed');
    }

    /**
     * 測試檢視訂單詳情
     */
    public function test_can_show_booking()
    {
        
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/admin/bookings/{$this->booking->id}");

        
        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->booking->id,
                'status' => 'pending'
            ]);
    }

    /**
     * 測試更新訂單狀態
     */
    public function test_can_update_booking_status()
    {
        $newStatus = 'confirmed';

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->putJson("/api/admin/bookings/{$this->booking->id}", [
                'status' => $newStatus
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', $newStatus);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => $newStatus
        ]);
    }
}
