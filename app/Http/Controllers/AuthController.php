<?php

namespace App\Http\Controllers;

use Exception;
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


    public function login(LoginRequest $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - Iniciando autenticación");
        
        try {
            $dto = AuthMapper::toAuthDTO($request);
            $authData = $this->authService->authenticate($dto);

            return response()->json([
                'message' => 'Success',
                'user'    => UserMapper::toResponse($authData['user'], $authData['data'])
            ]);

        } catch (Exception $e) {
            Log::channel('auth')->error('Error en el login', [
                'usuario' => $request->username,
                'error'   => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => $e->getMessage()
            ], in_array($e->getCode(), [401, 422, 502]) ? $e->getCode() : 500);
        }
    }


    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request);
            
            return response()->json(['message' => 'Sesión cerrada correctamente'])
                ->withCookie(cookie()->forget('laravel_session'))
                ->withCookie(cookie()->forget('XSRF-TOKEN'));

        } catch (Exception $e) {
            Log::channel('auth')->error('Error al cerrar sesión', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al cerrar sesión'], 500);
        }
    }


    public function verifyToken(Request $request): JsonResponse
    {
        Log::channel('auth')->info("Usuario: {$request->username} - Verificando token");
        try {
            $isValid = $this->authService->verifyToken(
                username: Auth::user()?->username ?? '',
                token: (string) session('tkg')
            );

            return response()->json([
                'valid'   => $isValid,
                'message' => $isValid ? 'Token válido' : 'Token inválido o expirado'
            ], $isValid ? 200 : 401);

        } catch (Exception $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 401);
        }
    }
}
