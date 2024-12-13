<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 測試系統使用者註冊
     */
    public function test_admin_can_register()
    {
        $payload = [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => '註冊成功',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('admins', ['email' => $payload['email']]);
    }

    /**
     * 測試註冊時驗證失敗
     */
    public function test_admin_register_validation_fails()
    {
        $payload = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $response = $this->postJson('/api/admin/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * 測試系統使用者登入
     */
    public function test_admin_can_login()
    {
        $user = Admin::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/admin/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '登入成功',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                ],
            ]);
    }

    /**
     * 測試登入時帳密錯誤
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        Admin::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'message' => '帳號或密碼錯誤',
            ]);
    }

    /**
     * 測試使用者登出
     */
    public function test_admin_can_logout()
    {
        $user = Admin::factory()->create();
        $token = $user->createToken('admins')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/admin/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => '登出成功',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * 測試取得個人資訊
     */
    public function test_admin_can_get_profile()
    {
        $user = Admin::factory()->create();
        $token = $user->createToken('admins')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/admin/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * 測試未授權請求無法取得個人資訊
     */
    public function test_unauthorized_admin_cannot_access_profile()
    {
        $response = $this->getJson('/api/admin/profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
