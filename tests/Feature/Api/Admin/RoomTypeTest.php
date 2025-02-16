<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Hotel;
use App\Models\RoomInventory;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RoomTypeTest extends TestCase
{
    use RefreshDatabase;

    protected $hotel;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create();
        $this->token = $admin->createToken('admins')->plainTextToken;

        $this->hotel = Hotel::factory()->create();
    }

    /**
     * 測試列出旅館的房型
     */
    public function test_can_list_room_types_for_a_hotel(): void
    {
        RoomType::factory()->count(3)->create([
            'hotel_id' => $this->hotel->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson("/api/admin/hotels/{$this->hotel->id}/room-types");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * 測試建立旅館的房型
     */
    public function test_can_create_room_type_for_a_hotel(): void
    {
        $roomTypeData = [
            'name' => '豪華海景房',
            'capacity' => 2,
            'base_price' => 5000.00,
            'description' => '擁有絕佳海景的豪華房型',
            'amenities' => [
                'wifi' => true,
                'airConditioner' => true,
                'oceanView' => true,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson("/api/admin/hotels/{$this->hotel->id}/room-types", $roomTypeData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => '豪華海景房',
                'capacity' => 2,
                'base_price' => 5000.00,
                'description' => '擁有絕佳海景的豪華房型',
            ]);

        $this->assertDatabaseHas('room_types', [
            'hotel_id' => $this->hotel->id,
            'name' => '豪華海景房',
        ]);
    }

    /**
     * 測試建立旅館的房型時驗證失敗
     */
    public function test_cannot_create_room_type_with_invalid_data(): void
    {
        $invalidRoomTypeData = [
            'name' => '', // 空名稱
            'capacity' => -1, // 無效容量
            'base_price' => -100, // 負數價格
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->postJson("/api/admin/hotels/{$this->hotel->id}/room-types", $invalidRoomTypeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'capacity',
                'base_price',
                'description',
            ]);
    }

    /**
     * 測試建立旅館的房型時驗證失敗
     */
    public function test_can_show_room_type_for_a_hotel(): void
    {
        $roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson("/api/admin/hotels/{$this->hotel->id}/room-types/{$roomType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $roomType->id,
                'name' => $roomType->name,
            ]);
    }

    public function test_can_show_room_type_with_query_for_a_hotel()
    {

        // 假設的房型資料
        $roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        // 假設的房間庫存資料
        $inventory1 = RoomInventory::factory()->create([
            'room_type_id' => $roomType->id,
            'date' => Carbon::create(2025, 1, 10), // 這是2025年1月的資料
        ]);

        $inventory2 = RoomInventory::factory()->create([
            'room_type_id' => $roomType->id,
            'date' => Carbon::create(2025, 2, 10), // 這是2025年2月的資料
        ]);

        // 測試請求，查詢2025年1月的房型
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson("/api/admin/hotels/{$this->hotel->id}/room-types/{$roomType->id}?year=2025&month=1");
       
        // 驗證結果
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $roomType->id,
        ]);

        $response->assertJsonCount(1, 'room_inventories'); // 應該只有1個庫存資料
        $response->assertJsonFragment([
            'date' => $inventory1->date->toDateString(),
        ]);
        $response->assertJsonMissing([
            'date' => $inventory2->date->toDateString(), // 確保2月的庫存未被包含
        ]);
    }

    /**
     * 測試更新旅館的房型
     */
    public function test_can_update_room_type_for_a_hotel(): void
    {
        $roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        $updatedData = [
            'name' => '更新後的房間類型',
            'capacity' => 3,
            'base_price' => 6000.00,
            'description' => '更新後的描述',
            'amenities' => [
                'wifi' => true,
                'breakfast' => true,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->putJson("/api/admin/hotels/{$this->hotel->id}/room-types/{$roomType->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => '更新後的房間類型',
                'capacity' => 3,
                'base_price' => 6000.00,
            ]);

        $this->assertDatabaseHas('room_types', [
            'id' => $roomType->id,
            'name' => '更新後的房間類型',
        ]);
    }
}
