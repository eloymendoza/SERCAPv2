<?php

namespace App\App\Api\Autenticacion\Controllers;


use App\Logging\LogContext;
use App\App\Api\Controller;
use Illuminate\Http\JsonResponse;
use App\Domain\Autenticacion\Services\AuthService;
use App\App\Api\Autenticacion\Requests\LoginRequest;
use App\App\Api\Autenticacion\Resources\UserResource;

/**
 * Orquestación de autenticación.
 * 
 * Gestiona el ciclo de vida de la sesión (login, logout, verificación) 
 * consumiendo servicios de backend y sincronizando el estado local.
 */

class AuthController extends Controller
{
    /**
     * @param AuthService $authService Lógica de autenticación y validación externa.
     */
    public function __construct(
        private readonly AuthService $authService,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('auth');
    }


    /**
     * Ejecuta el inicio de sesión y regenera la sesión local.
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws \App\Domain\Autenticacion\Exceptions\AuthException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Auth::attempt delega automáticamente al DjangoUserProvider
        if (!\Illuminate\Support\Facades\Auth::attempt($request->only('username', 'password'))) {
            throw \App\Domain\Autenticacion\Exceptions\AuthException::invalidCredentials('Credenciales inválidas.');
        }

        $request->session()->regenerate();

        // El DTO fue hidratado en memoria por el Provider
        $userDto = \Illuminate\Support\Facades\Auth::user()->contexto;

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'data'    => new UserResource($userDto)
        ]);
    }


    /**
     * Finaliza la sesión actual y limpia estados locales.
     * 
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        
        return response()->json(['message' => 'Sesión cerrada correctamente'])
            ->withCookie(cookie()->forget('laravel_session'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }


    /**
     * Recupera el estado del usuario autenticado en la sesión.
     * 
     * @return JsonResponse
     */
    public function checkSession(): JsonResponse
    {
        if (!\Illuminate\Support\Facades\Auth::check() || !\Illuminate\Support\Facades\Auth::user()->contexto) {
            throw new \App\Domain\Autenticacion\Exceptions\AuthException('Sesión no encontrada o expirada.');
        }

        $userDto = \Illuminate\Support\Facades\Auth::user()->contexto;

        return response()->json([
            'success' => true,
            'message' => 'Sesión verificada correctamente.',
            'data'    => new UserResource($userDto)
        ]);
    }
}