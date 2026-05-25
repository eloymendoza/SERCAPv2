<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SolicitudRequisicionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/solicitud-requisiciones', SolicitudRequisicionController::class);
    
    Route::middleware(['verify.django'])->group(function () {
        Route::get('/me', [AuthController::class, 'checkSession']);
    });
});

Route::post('/login', [AuthController::class, 'login']);