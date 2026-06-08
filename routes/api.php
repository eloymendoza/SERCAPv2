<?php

use Illuminate\Support\Facades\Route;
use App\App\Api\Controllers\AuthController;
use App\App\Api\Controllers\SolicitudRequisicionController;
use App\App\Api\Controllers\ProyectoController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/solicitud-requisiciones', SolicitudRequisicionController::class);
    
    Route::get('/proyectos', [ProyectoController::class, 'index']);
    
    Route::middleware(['verify.django'])->group(function () {
        Route::get('/me', [AuthController::class, 'checkSession']);
    });
});

Route::post('/login', [AuthController::class, 'login']);