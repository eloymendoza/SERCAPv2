<?php

namespace App\Http\Controllers;

use App\Mappers\AuthMapper;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly AuthMapper $authMapper
    ) {}


    public function login(LoginRequest $request): JsonResponse
    {
        $dto = $this->authMapper->toDTO($request);
        $userDto = $this->authService->authenticate($dto);

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'data'    => new UserResource($userDto)
        ]);
    }


    public function logout(): JsonResponse
    {
        $this->authService->logout();
        
        return response()->json(['message' => 'Sesión cerrada correctamente'])
            ->withCookie(cookie()->forget('laravel_session'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }


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