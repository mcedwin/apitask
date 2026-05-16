<?php

use Illuminate\Support\Facades\Route; // <--- FALTA ESTO
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ObjetiveController;
use App\Http\Controllers\NotificationController;

Route::post('/send-notification', [NotificationController::class, 'send']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'google']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/objetives', [ObjetiveController::class, 'store']);
    Route::get('/me', fn ($request) => $request->user());
    Route::get('/objetivos', [ObjetiveController::class, 'index']);
    Route::post('/objetivos', [ObjetiveController::class, 'store']);
    Route::put('/objetivos/{id}', [ObjetiveController::class, 'update']);
    Route::delete('/objetivos/{id}', [ObjetiveController::class, 'destroy']);
});

Route::post('/auth/register', [AuthController::class, 'register']);