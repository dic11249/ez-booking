<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AmenityController;
use App\Http\Controllers\Api\Admin\HotelController;
use App\Http\Controllers\Api\Admin\RoomTypeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);

Route::middleware('auth:admins')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/profile', [AdminController::class, 'profile']);
});

/** 旅館 */
Route::apiResource('hotels', HotelController::class);

/** 旅館房間 */
Route::get('/hotels/{hotel}/room-types', [RoomTypeController::class, 'index']);
Route::post('/hotels/{hotel}/room-types', [RoomTypeController::class, 'store']);
Route::get('/hotels/{hotel}/room-types/{roomType}', [RoomTypeController::class, 'show']);
Route::put('/hotels/{hotel}/room-types/{roomType}', [RoomTypeController::class, 'update']);

/** 設施 */
Route::apiResource('amenities', AmenityController::class);
