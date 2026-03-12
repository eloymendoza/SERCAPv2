<?php

namespace App\Services;

use App\DTOs\AuthDTO;
use App\Mappers\UserMapper;
use Illuminate\Http\Request;
use App\Clients\DjangoAuthClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Actions\Auth\SyncSessionAction;
use App\Actions\Auth\CompleteLogoutAction;
use App\Exceptions\Domain\AuthException;
use App\Traits\HandlesProcess;

class AuthService
{
    use HandlesProcess;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DjangoAuthClient $authClient,
        private readonly SyncSessionAction $syncSessionAction,
        private readonly CompleteLogoutAction $completeLogoutAction
    ) {}


    /**
     * Proceso de autenticación orquestado.
     */
    public function authenticate(AuthDTO $dto): array
    {
        return $this->handle(function () use ($dto) {
            // 1. Consultar API Django vía Cliente
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                Log::channel('auth')->error("Fallo de respuesta HTTP en Django", [
                    'response' => $response->json()
                ]);
                
                // Si la API devuelve un mensaje, lo respetamos pero mediante una excepción de dominio
                throw AuthException::invalidCredentials('Credenciales inválidas. Por favor, inténtalo de nuevo.');
            }

            $data = $response->json();

            // 2. Validar respuesta de negocio
            if (($data['message'] ?? '') !== 'Success') {
                throw AuthException::invalidCredentials($data['message'] ?? 'Credenciales inválidas');
            }

            Log::channel('auth')->info("Contenido de data de Django: ", $data);

            // 3. Sincronizar usuario local (Repository)
            $mappedData = UserMapper::fromDjangoToLocal($data);
            $user = $this->userRepository->syncExternalUser($mappedData);

            // 4. Control de Sesiones Concurrentes (Nivel Elite)
            DB::table('sessions')->where('user_id', $user->id)->delete();

            // 5. Autenticación en Laravel
            Auth::login($user);
            request()->session()->regenerate();

            // 6. Sincronizar sesión vía Acción
            $this->syncSessionAction->execute($data);

            return [
                'user' => $user,
                'data' => $data
            ];
        }, 'AuthService@authenticate');
    }


    /**
     * Cierre de sesión orquestado.
     */
    public function logout(Request $request): void
    {
        $this->handle(function () use ($request) {
            $username = Auth::user()?->username;

            // 1. Inactivar Token en Django vía Cliente
            if ($username) {
                $response = $this->authClient->invalidateToken($username);
                if ($response->successful() && ($response->json()['status'] ?? '') === 'Success') {
                    Log::channel('auth')->info("Token inactivado correctamente para {$username}");
                } else {
                    Log::channel('auth')->error("Fallo al inactivar token para {$username}");
                }
            }

            // 2. Cierre de sesión y limpieza de Laravel vía Acción
            $this->completeLogoutAction->execute($request);
        }, 'AuthService@logout');
    }


    /**
     * Verificación de token orquestada.
     */
    public function verifyToken(string $username, string $token): bool
    {
        return $this->handle(function () use ($username, $token) {
            $response = $this->authClient->verifyToken($username, $token);

            if ($response->successful()) {
                return ($response->json()['message'] ?? '') === 'Success';
            }

            return false;
        }, 'AuthService@verifyToken');
    }
}