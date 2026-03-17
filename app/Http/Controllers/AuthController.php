<?php

namespace App\Http\Controllers;

use App\Mappers\AuthMapper;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}



    public function login(LoginRequest $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - AuthController::login");
        
        $dto = (new AuthMapper())->toDTO($request);
        $authData = $this->authService->authenticate($dto);

        Log::channel('auth')->info("Datos de usuario obtenidos correctamente: ", $authData);

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'data'    => $authData['data']
        ]);
    }


    public function logout(Request $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - AuthController::logout");

        $this->authService->logout($request);
        
        return response()->json(['message' => 'Sesión cerrada correctamente'])
            ->withCookie(cookie()->forget('laravel_session'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }


    public function checkSession(Request $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: " . Auth::user()?->username . " - AuthController::checkSession");
        
        $authData = $this->authService->checkSession($request);

        return response()->json([
            'success' => true,
            'message' => 'Sesión verificada correctamente.',
            'data'    => $authData['sessionData']
        ]);
    }
}