<?php

namespace App\Services;

use App\DTOs\AuthDTO;
use App\DTOs\UserDTO;
use App\Mappers\UserMapper;
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
        private readonly ClearUserSessionsAction $clearUserSessionsAction,
        private readonly UserMapper $userMapper
    ) {}


    /**
     * Proceso de autenticación orquestado.
     */
    public function authenticate(AuthDTO $dto): UserDTO
    {
        Log::channel('auth')->info("Usuario: {$dto->username} - AuthService::authenticate");
        
        return $this->handle(function () use ($dto) {
            // Autenticar en API Externa
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                throw AuthException::invalidCredentials('Credenciales inválidas o error de conexión.');
            }

            $data = $response->json();
            Log::channel('auth')->info("Proceso de autenticación exitoso: ", [
                'data' => $data
            ]);

            // Sincronizar datos base (incluyendo token)
            $userDto = $this->userMapper->fromDjangoToDTO($data);
            
            // Persistir localmente
            $user = $this->userRepository->syncExternalUser($this->userMapper->toPersistenceArray($userDto));

            // Autenticar en Laravel
            Auth::login($user);
            request()->session()->regenerate();

            // Actualizar DTO con ID local para la sesión
            $fullDto = $userDto->withId($user->id);
            Log::channel('auth')->info("DTO completo actualizado: ", [
                'data' => $fullDto
            ]);

            // Sincronizar sesión usando DTO
            $this->syncSessionAction->execute($fullDto);

            // Limpiar sesiones anteriores
            $this->clearUserSessionsAction->execute($user->id);

            // Retornar DTO para el controlador
            $sessionData = $this->getAuthenticatedUserAction->execute();

            return $this->userMapper->toDTO($sessionData);
        }, 'AuthService@authenticate');
    }


    /**
     * Cierre de sesión orquestado.
     */
    public function logout(): void
    {
        Log::channel('auth')->info("Usuario: " . Auth::user()?->username . " - AuthService::logout");

        $this->handle(function () {
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
            $this->completeLogoutAction->execute(request());
        }, 'AuthService@logout');
    }


    /**
     * Obtiene los datos del usuario autenticado y valida la vigencia del token externo.
     */
    public function checkSession(): UserDTO
    {
        Log::channel('auth')->info("Usuario: " . Auth::user()?->username . " - AuthService::checkSession");
        
        return $this->handle(function () {
            $username = Auth::user()?->username;

            // Validar identidad en API Auth
            if (!$this->verifyToken($username)) {
                Log::channel('auth')->warning("Sesión invalidada por token expirado/inválido: {$username}");
                
                // Aplicar Auto-Logout Proactivo
                $this->completeLogoutAction->execute(request());
                
                throw AuthException::invalidCredentials('Tu sesión ha expirado por seguridad.');
            }   

            // Extraer metadatos de sesión
            $sessionData = $this->getAuthenticatedUserAction->execute();

            return $this->userMapper->toDTO($sessionData);
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