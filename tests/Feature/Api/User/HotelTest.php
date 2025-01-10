<?php

namespace Tests\Feature\Api\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('users')->plainTextToken;

        // 創建一些基本的測試資料
        Hotel::factory()->count(3)->create([
            'is_active' => true
        ]);

        // 創建一個未啟用的飯店
        Hotel::factory()->create([
            'is_active' => false
        ]);
    }

    /**
     * 測試取得所有有效飯店
     */
    public function test_can_get_all_active_hotels()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/hotels');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * 測試使用者可以透過名稱搜尋飯店
     */
    public function test_can_filter_hotels_by_name()
    {
        $hotel = Hotel::factory()->create([
            'name' => 'Unique Grand Hotel',
            'is_active' => true
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/hotels?name=Unique');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $hotel->name);
    }

    /**
     *  測試使用者可以透過城市搜尋飯店
     */
    public function test_can_filter_hotels_by_city()
    {
        $hotel = Hotel::factory()->create([
            'city' => 'Tokyo',
            'is_active' => true
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/hotels?city=Tokyo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.city', 'Tokyo');
    }

    /**
     * 測試使用者查看飯店詳情
     */
    public function test_can_show_hotel()
    {
        $hotel = Hotel::factory()->create([
            'is_active' => true
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/hotels/{$hotel->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $hotel->id,
                'name' => $hotel->name,
                'city' => $hotel->city,
                'country' => $hotel->country,
                'rating' => $hotel->rating
            ]);
    }

    /**
     * 測試使用者無法取得未啟用的飯店
     */
    public function user_cannot_get_inactive_hotel()
    {
        $hotel = Hotel::factory()->create([
            'is_active' => false
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/hotels/{$hotel->id}");

        $response->assertStatus(404);
    }

    /**
     * 測試使用者無法取得不存在的飯店
     */
    public function returns_404_for_non_existent_hotel()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/hotels/9999');

        $response->assertStatus(404);
    }
}
