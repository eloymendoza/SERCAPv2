<?php

namespace App\Services;

use App\DTOs\AuthDTO;
use App\Mappers\UserMapper;
use Illuminate\Http\Request;
use App\Traits\HandlesProcess;
use App\Clients\DjangoAuthClient;
use Illuminate\Support\Facades\Log;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Actions\Auth\SyncSessionAction;
use App\Exceptions\Domain\AuthException;
use App\Actions\Auth\CompleteLogoutAction;
use App\Actions\Auth\ClearUserSessionsAction;
use App\Actions\Auth\GetAuthenticatedUserAction;

class AuthService
{
    use HandlesProcess;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DjangoAuthClient $authClient,
        private readonly SyncSessionAction $syncSessionAction,
        private readonly CompleteLogoutAction $completeLogoutAction,
        private readonly GetAuthenticatedUserAction $getAuthenticatedUserAction,
        private readonly ClearUserSessionsAction $clearUserSessionsAction
    ) {}


    /**
     * Proceso de autenticación orquestado.
     */
    public function authenticate(AuthDTO $dto): array
    {
        Log::channel('auth')->info("Usuario: {$dto->username} - AuthService::authenticate");
        
        return $this->handle(function () use ($dto) {
            // Autenticar en API Externa
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                throw AuthException::invalidCredentials('Credenciales inválidas o error de conexión.');
            }

            $data = $response->json();

            // Sincronizar usuario local
            $userDto = (new UserMapper())->fromDjangoToDTO($data);
            $user = $this->userRepository->syncExternalUser((new UserMapper)->toPersistenceArray($userDto));

            // Limpiar sesiones anteriores (Garantizar sesión única)
            $this->clearUserSessionsAction->execute($user->id);

            // Autenticar en Laravel y sincronizar sesión
            Auth::login($user);
            request()->session()->regenerate();
            $this->syncSessionAction->execute($data);

            // Retornar datos de respuesta
            $sessionData = $this->getAuthenticatedUserAction->execute();

            return ['data' => (new UserMapper)->toResponseArray((new UserMapper)->toDTO($sessionData))];
        }, 'AuthService@authenticate');
    }


    /**
     * Cierre de sesión orquestado.
     */
    public function logout(Request $request): void
    {
        Log::channel('auth')->info("Usuario: {$request->username} - AuthService::logout");
        $this->handle(function () use ($request) {
            $username = Auth::user()?->username;

            // Inactivar Token en API Auth
            if ($username) {
                $response = $this->authClient->invalidateToken($username);
                if ($response->successful() && ($response->json()['status'] ?? '') === 'Success') {
                    Log::channel('auth')->info("Token inactivado correctamente para {$username}");
                } else {
                    Log::channel('auth')->error("Fallo al inactivar token para {$username}");
                }
            }

            // Cierre de sesión y limpieza de Laravel
            $this->completeLogoutAction->execute($request);
        }, 'AuthService@logout');
    }


    /**
     * Obtiene los datos del usuario autenticado y valida la vigencia del token externo.
     */
    public function checkSession(Request $request): array
    {
        Log::channel('auth')->info("Usuario: " . Auth::user()?->username . " - AuthService::checkSession");
        
        return $this->handle(function () use ($request) {
            $username = Auth::user()?->username;

            // Validar identidad en API Auth
            if (!$this->verifyToken($username)) {
                Log::channel('auth')->warning("Sesión invalidada por token expirado/inválido: {$username}");
                
                // Aplicar Auto-Logout Proactivo
                $this->completeLogoutAction->execute($request);
                
                throw AuthException::invalidCredentials('Tu sesión ha expirado por seguridad.');
            }   

            // Extraer metadatos de sesión
            $sessionData = $this->getAuthenticatedUserAction->execute();

            return [
                'user' => Auth::user(),
                'sessionData' => (new UserMapper)->toResponseArray((new UserMapper)->toDTO($sessionData))
            ];
        }, 'AuthService@checkSession');
    }


    /**
     * Verificación de token orquestada contra API externa.
     */
    public function verifyToken(string $username): bool
    {
        return $this->handle(function () use ($username) {
            $token = (string) Session::get('token');
            
            if (empty($token)) {
                return false;
            }

            $response = $this->authClient->verifyToken($username, $token);
            
            $isValid = $response->successful() && ($response->json()['message'] ?? '') === 'Success';

            if (!$isValid) {
                Log::channel('auth')->error("Fallo de validación de token en Django para {$username}", [
                    'response' => $response->json()
                ]);
            }

            return $isValid;
        }, 'AuthService@verifyToken');
    }
}
