<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public auth endpoints
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated auth endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// Courier CRUD — semua butuh token Sanctum, write operations butuh role admin
Route::middleware('auth:sanctum')->apiResource('couriers', CourierController::class);
