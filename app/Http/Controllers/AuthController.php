<?php

namespace App\Http\Controllers;

use App\Mappers\AuthMapper;
use App\Mappers\UserMapper;
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


    /**
     * Proceso de Login profesional.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - Iniciando autenticación");
        
        $dto = AuthMapper::toAuthDTO($request);
        $authData = $this->authService->authenticate($dto);

        return response()->json([
            'success' => true,
            'message' => 'Autenticación exitosa',
            'user'    => UserMapper::toResponse($authData['user'], $authData['data'])
        ]);
    }

    /**
     * Cierre de sesión seguro.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);
        
        return response()->json(['message' => 'Sesión cerrada correctamente'])
            ->withCookie(cookie()->forget('laravel_session'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }

    /**
     * Verificación de token.
     */
    public function verifyToken(Request $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - Verificando token");
        
        $isValid = $this->authService->verifyToken(
            username: Auth::user()?->username ?? '',
            token: (string) session('tkg')
        );

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Token válido' : 'Token inválido o expirado'
        ]);
    }
}
