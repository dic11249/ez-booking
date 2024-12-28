<?php

namespace Tests\Feature\Api\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Hotel;
use App\Models\Amenity;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelTest extends TestCase
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
     * 測試取得飯店清單 (成功)
     */
    public function test_can_get_hotel_list()
    {
        // 建立測試資料
        Hotel::factory()->count(5)->create();

        // 模擬 API 呼叫
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson('/api/admin/hotels');

        // 驗證回應
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'total',
            ]);
    }

    /**
     * 測試篩選飯店清單 (成功)
     */
    public function test_can_filter_hotel_list_by_city()
    {
        // 建立測試資料
        Hotel::factory()->create(['city' => 'Tokyo']);
        Hotel::factory()->create(['city' => 'Osaka']);

        // 篩選條件
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])
        ->getJson('/api/admin/hotels?city=Tokyo');

        // 驗證回應
        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Tokyo'])
            ->assertJsonMissing(['city' => 'Osaka']);
    }

    /**
     * 測試新增飯店 (成功)
     */
    public function test_can_create_hotel()
    {
        // 建立測試 amenities
        $amenities = Amenity::factory()->count(3)->create();

        $data = [
            'name' => 'Hotel Test',
            'description' => 'Test Description',
            'address' => '123 Test Road',
            'city' => 'Test City',
            'country' => 'Test Country',
            'latitude' => 35.6895,
            'longitude' => 139.6917,
            'total_rooms' => 50,
            'rating' => 4.5,
            'is_active' => true,
            'amenities' => $amenities->pluck('id')->toArray(),
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])
        ->postJson('/api/admin/hotels', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Hotel Test']);

        $this->assertDatabaseHas('hotels', ['name' => 'Hotel Test']);

        $this->assertDatabaseCount('hotel_amenities', 3);
    }

    /**
     * 測試檢視飯店詳情 (成功)
     */
    public function test_can_show_hotel()
    {
        $hotel = Hotel::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])
        ->getJson("/api/admin/hotels/$hotel->id");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $hotel->name]);
    }

    /**
     * 測試更新飯店 (成功)
     */
    public function test_can_update_hotel()
    {
        $hotel = Hotel::factory()->create();
        $oldAmenities = Amenity::factory()->count(2)->create();
        $hotel->amenities()->sync($oldAmenities->pluck('id')->toArray());

        $newAmenities = Amenity::factory()->count(3)->create();

        $data = [
            'name' => 'Updated Hotel Name',
            'description' => 'Updated Description',
            'address' => 'Updated Address',
            'city' => 'Updated City',
            'country' => 'Updated Country',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'total_rooms' => 80,
            'rating' => 4.7,
            'is_active' => false,
            'amenities' => $newAmenities->pluck('id')->toArray(),
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->putJson('/api/admin/hotels/' . $hotel->id, $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Hotel Name']);

        $this->assertDatabaseHas('hotels', ['name' => 'Updated Hotel Name']);

        // 驗證 amenities 更新
        $this->assertDatabaseCount('hotel_amenities', 3);
        foreach ($newAmenities as $amenity) {
            $this->assertDatabaseHas('hotel_amenities', [
                'hotel_id' => $hotel->id,
                'amenity_id' => $amenity->id,
            ]);
        }
    }

    /**
     * 測試刪除飯店 (成功)
     */
    public function test_can_delete_hotel()
    {
        $hotel = Hotel::factory()->create();

        $response = $this->withHeaders([
           'Authorization' => "Bearer $this->token",
        ])->deleteJson("/api/admin/hotels/$hotel->id");

        $response->assertStatus(204);

        $this->assertSoftDeleted('hotels', ['id' => $hotel->id]);
    }

    /**
     * 測試檢視不存在的飯店 (失敗)
     */
    public function test_show_nonexistent_hotel_returns_404()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->getJson('/api/admin/hotels/999');

        $response->assertStatus(404);
    }
}
