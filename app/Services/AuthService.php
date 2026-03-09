<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Mappers\UserMapper;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * Lógica de autenticación contra la API de Django y sincronización local.
     * @throws Exception
     */
    public function authenticate(LoginDTO $dto): array
    {
        $payload = [
            'token'     => base64_encode(env('SECRET_KEY')), 
            'user'      => $dto->username,
            'password'  => base64_encode($dto->password), 
            'idSistema' => env('ID_SISTEMA'),
            'timeExp'   => 1,
            'saveTk'    => 1
        ];

        // Consultar API Django
        $response = Http::timeout(30)
            ->withOptions(['verify' => app()->environment('local')])
            ->post(env('API_AUTH'), $payload);

        if (!$response->successful()) {
            $this->logError('Fallo de respuesta HTTP en Django', $response, $dto->username);
            throw new Exception($response->json()['message'] ?? 'Error en el servidor de autenticación', $response->status());
        }

        $data = $response->json();

        // Validar respuesta "Success" de Django
        if (($data['message'] ?? '') !== 'Success') {
            $this->logError('Django rechazó las credenciales', $response, $dto->username);
            throw new Exception($data['message'] ?? 'Credenciales inválidas', 401);
        }

        Log::info("Contenido de data: ", $data);

        // Sincronizar usuario localmente usando el Mapper
        $mappedData = UserMapper::fromDjangoToLocal($data);
        $user = $this->userRepository->syncExternalUser($mappedData);

        // Iniciar sesión en Laravel
        Auth::login($user);

        // Retornar datos para el controlador
        return [
            'user' => $user,
            'data' => $data
        ];
    }

    /**
     * Cierra la sesión activa en Laravel.
     */
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        Log::channel('auth')->info('Sesión cerrada exitosamente');
    }

    private function logError(string $message, $response, string $username): void
    {
        Log::channel('auth')->error($message, [
            'status' => $response->status(),
            'usuario' => $username,
            'body' => $response->json()
        ]);
    }
}