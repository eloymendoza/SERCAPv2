<?php

namespace App\Http\Controllers;

use Exception;
use App\DTOs\LoginDTO;
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
     * Inicia sesión autenticando contra Django y sincronizando el usuario.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        Log::channel('auth')->info('Iniciando sesión');
        Log::channel('auth')->info('=== LOGIN DEBUG ===', [
            'session_id_generada' => $request->session()->getId(),
        ]);
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
            Log::channel('auth')->error('Error en el login', [
                'usuario' => $request->username,
                'error'   => $e->getMessage()
            ]);
            return response()->json([
                'message' => $e->getMessage()
            ], in_array($e->getCode(), [401, 422, 502]) ? $e->getCode() : 500);
        }
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request);
            
            // Creamos la respuesta y eliminamos la cookie explícitamente
            return response()
                ->json(['message' => 'Sesión cerrada correctamente'])
                ->withCookie(cookie()->forget('laravel_session'))
                ->withCookie(cookie()->forget('XSRF-TOKEN'));

        } catch (Exception $e) {
            Log::channel('auth')->error('Error al cerrar sesión', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al cerrar sesión'], 500);
        }
    }

    /**
     * Obtiene los datos del usuario autenticado y su sesión.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'session' => [
                'permisos'       => session('permisos'),
                'tkg'            => session('tkg'),
                'idPersonal'     => session('idPersonal'),
                'nombreCompleto' => session('nombreCompleto'),
            ]
        ]);
    }

    /**
     * Endpoint de prueba para verificar la persistencia de la sesión.
     */
    public function sessionTest(): JsonResponse
    {
        return response()->json([
            'is_logged_in' => Auth::check(),
            'user'         => Auth::user(),
            'session_data' => session()->all(),
        ]);
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
