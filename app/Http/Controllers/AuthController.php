<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\DTOs\LoginDTO;
use App\Mappers\UserMapper;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Inicia sesión autenticando contra Django y sincronizando el usuario.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginDTO::fromRequest($request);
            $authData = $this->authService->authenticate($dto);

            // Guardar datos adicionales en la sesión
            $this->syncSession($authData['data']);

            return response()->json([
                'message' => 'Success',
                'user'    => UserMapper::toResponse($authData['user'], $authData['data'])
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], in_array($e->getCode(), [401, 422, 502]) ? $e->getCode() : 500);
        }
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return response()->json(['message' => 'Sesión cerrada correctamente']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al cerrar sesión'], 500);
        }
    }

    /**
     * Sincroniza los datos de la sesión de Laravel con la respuesta de Django.
     */
    private function syncSession(array $data): void
    {
        session([
            'permisos'       => $data['permisos'] ?? [],
            'tkg'            => $data['tkg'] ?? null,
            'idPersonal'     => $data['idPersonal'] ?? null,
            'nombreCompleto' => $data['nombreCompleto'] ?? null,
            'rutaFoto'       => $data['rutaFoto'] ?? null
        ]);
    }
}
