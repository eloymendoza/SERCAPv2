<?php

use Illuminate\Support\Facades\Route;
use App\App\Api\Autenticacion\Controllers\AuthController;
use App\App\Api\Proyectos\Controllers\ProyectoController;
use App\App\Api\GestorCVs\Controllers\AspiranteController;
use App\App\Api\Requisiciones\Controllers\SolicitudRequisicionController;
use App\App\Api\EstructuraOrganizacional\Controllers\UnidadOrganizativaController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/solicitud-requisiciones', SolicitudRequisicionController::class)
        ->parameters(['solicitud-requisiciones' => 'solicitud']);

    Route::get('/solicitud-requisiciones/{solicitud}/preview-aprobadores', [SolicitudRequisicionController::class, 'previewAprobadores']);

    Route::apiResource('/unidades-organizativas', UnidadOrganizativaController::class)
        ->parameters(['unidades-organizativas' => 'unidad']);
        
    Route::get('/proyectos', [ProyectoController::class, 'index']);
    
    Route::middleware(['verify.django'])->group(function () {
        Route::get('/me', [AuthController::class, 'checkSession']);
    });
});

Route::post('/login', [AuthController::class, 'login']);

// Temporal para desarrollo — mover dentro del middleware antes de subir a producción
Route::prefix('aspirantes')->group(function () {
    Route::post('/',            [AspiranteController::class, 'store']);
    Route::get('/{aspirante}', [AspiranteController::class, 'show']);
});