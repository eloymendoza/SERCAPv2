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
use App\Logging\LogContext;
use App\Actions\Auth\SyncSessionAction;
use App\Exceptions\Domain\AuthException;
use App\Actions\Auth\CompleteLogoutAction;
use App\Actions\Auth\ClearUserSessionsAction;
use App\Actions\Auth\GetAuthenticatedUserAction;

/**
 * Servicio de Autenticación.
 * 
 * Gestiona la integración con la API Auth, sincronización de usuarios locales,
 * persistencia de sesiones y validación de tokens.
 */
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
        private readonly UserMapper $userMapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'auth';
    }


    /**
     * Orquestación de inicio de sesión y sincronización local.
     * 
     * @param AuthDTO $dto
     * @return UserDTO
     * @throws AuthException
     */
    public function authenticate(AuthDTO $dto): UserDTO
    {
        Log::channel($this->logContext->channel())->info("Usuario: {$dto->username} - AuthService::authenticate");
        
        return $this->handle(function () use ($dto) {
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                throw AuthException::invalidCredentials('Credenciales inválidas o error de conexión.');
            }

            $data = $response->json();
            Log::channel($this->logContext->channel())->info("Proceso de autenticación exitoso: ", [
                'data' => $data
            ]);

            $userDto = $this->userMapper->fromDjangoToDTO($data);
            $user = $this->userRepository->syncExternalUser($this->userMapper->toPersistenceArray($userDto));

            Auth::login($user);
            $this->logContext->setUsername($user->username);

            $fullDto = $userDto->withId($user->id);
            Log::channel($this->logContext->channel())->info("DTO completo actualizado: ", [
                'data' => $fullDto
            ]);

            $this->syncSessionAction->execute($fullDto);
            $this->clearUserSessionsAction->execute($user->id);

            $sessionData = $this->getAuthenticatedUserAction->execute();

            return $this->userMapper->toDTO($sessionData);
        }, 'AuthService@authenticate');
    }


    /**
     * Invalida la sesión local y el estado del token externo.
     */
    public function logout(): void
    {
        Log::channel($this->logContext->channel())->info("Usuario: " . Auth::user()?->username . " - AuthService::logout");

        $this->handle(function () {
            $username = Auth::user()?->username;

            if ($username) {
                $response = $this->authClient->invalidateToken($username);
                if ($response->successful() && ($response->json()['status'] ?? '') === 'Success') {
                    Log::channel($this->logContext->channel())->info("Token inactivado correctamente para {$username}");
                } else {
                    Log::channel($this->logContext->channel())->error("Fallo al inactivar token para {$username}");
                }
            }

            $this->completeLogoutAction->execute(request());
        }, 'AuthService@logout');
    }


    /**
     * Recupera la información del usuario desde la sesión persistida.
     * 
     * @return UserDTO
     */
    public function checkSession(): UserDTO
    {
        Log::channel($this->logContext->channel())->info("Usuario: " . Auth::user()?->username . " - AuthService::checkSession");
        
        return $this->handle(function () {
            $sessionData = $this->getAuthenticatedUserAction->execute();
            Log::channel($this->logContext->channel())->info("Datos de sesión extraídos: ", [
                'data' => $sessionData
            ]);
            return $this->userMapper->toDTO($sessionData);
        }, 'AuthService@checkSession');
    }


    /**
     * Verifica la validez del token contra el servicio externo.
     * 
     * @param string $username
     * @return bool
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
                Log::channel($this->logContext->channel())->error("Fallo de validación de token en Django para {$username}", [
                    'response' => $response->json()
                ]);
            }

            return $isValid;
        }, 'AuthService@verifyToken');
    }
}