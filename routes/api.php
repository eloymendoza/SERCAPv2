<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/verify-token', [AuthController::class, 'verifyToken']);
});

Route::post('/login', [AuthController::class, 'login']);