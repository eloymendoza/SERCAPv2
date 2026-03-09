<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ruta para probar si la sesión persiste
    Route::get('/session-test', function () {
        return response()->json([
            'is_logged_in' => Auth::check(),
            'user' => Auth::user(),
            'session_data' => session()->all(),
        ]);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
