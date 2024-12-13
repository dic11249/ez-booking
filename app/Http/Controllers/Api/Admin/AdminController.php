<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/register",
     *     summary="註冊新系統使用者",
     *     description="建立新的系統使用者帳戶",
     *     tags={"Admin"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="註冊成功",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="註冊成功"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="驗證失敗")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => '註冊成功',
            'data' => $admin,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/login",
     *     summary="系統使用者登入",
     *     description="登入並獲取 Token",
     *     tags={"Admin"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="登入成功",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="登入成功"),
     *             @OA\Property(property="access_token", type="string", example="token_string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="認證失敗")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = Admin::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        $token = $user->createToken('admins')->plainTextToken;

        return response()->json([
            'message' => '登入成功',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/logout",
     *     summary="系統使用者登出",
     *     description="登出並刪除 Token",
     *     tags={"Admin"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="登出成功",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="登出成功")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/profile",
     *     summary="獲取系統使用者個人資訊",
     *     description="返回目前系統登入使用者的個人資訊",
     *     tags={"Admin"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="個人資訊",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function profile(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
        ], 200);
    }
}
