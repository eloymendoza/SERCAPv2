<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::middleware(['verify.django'])->group(function () {
        Route::get('/me', [AuthController::class, 'checkSession']);
    });
});

Route::post('/login', [AuthController::class, 'login']);