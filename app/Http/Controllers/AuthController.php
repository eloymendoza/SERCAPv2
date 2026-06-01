<?php

namespace App\Http\Controllers;


use App\Logging\LogContext;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

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
     * @throws \App\Exceptions\Domain\AuthException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = $request->toDTO();
        $userDto = $this->authService->authenticate($dto);

        // La regeneración de sesión es una preocupación de HTTP/Controlador
        $request->session()->regenerate();

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
        $userDto = $this->authService->checkSession();

        return response()->json([
            'success' => true,
            'message' => 'Sesión verificada correctamente.',
            'data'    => new UserResource($userDto)
        ]);
    }
}