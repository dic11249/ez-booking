<?php

namespace Tests\Feature\Api\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\RoomType;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoomInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create();
        $this->token = $admin->createToken('admins')->plainTextToken;
    }

    /**
     * A basic feature test example.
     */
    public function test_can_create_or_update_room_inventory(): void
    {
        // 創建一個房型
        $roomType = RoomType::factory()->create();

        $testDay = Carbon::now()->addDay()->format('Y-m-d');

        // 請求資料
        $data = [
            'room_type_id' => $roomType->id,
            'date' => $testDay,
            'total_rooms' => 100,
        ];

        // 發送 POST 請求
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson("/api/admin/room-types/{$roomType}/inventories", $data);

        // 驗證回應
        $response->assertStatus(201); // 201狀態碼
        $response->assertJsonFragment([
            'room_type_id' => $roomType->id,
            'date' => $testDay,
            'total_rooms' => 100,
            'booked_rooms' => 0, // 預設已預訂房間數為0
        ]);

        // 檢查庫存是否正確創建
        $this->assertDatabaseHas('room_inventories', [
            'room_type_id' => $roomType->id,
            'date' => $testDay,
            'total_rooms' => 100,
            'booked_rooms' => 0,
        ]);
    }
}
