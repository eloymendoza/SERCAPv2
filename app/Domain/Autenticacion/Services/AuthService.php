<?php

namespace App\Domain\Autenticacion\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Domain\Autenticacion\Models\User;
use App\Domain\Autenticacion\DTOs\AuthDTO;
use App\Domain\Autenticacion\DTOs\UserDTO;
use Illuminate\Auth\AuthenticationException;
use App\Domain\Autenticacion\Mappers\UserMapper;
use App\Infrastructure\Clients\DjangoAuthClient;
use App\Domain\Autenticacion\Exceptions\AuthException;

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
        private readonly DjangoAuthClient $authClient,
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
        $this->logger()->info("Iniciando autenticación.", [
            'username' => $dto->username
        ]);
        
        return $this->handle(function () use ($dto) {
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                throw AuthException::invalidCredentials('Credenciales inválidas o error de conexión.');
            }

            $data = $response->json();
            $this->logger()->info("Autenticación exitosa.", [
                'data' => $data
            ]);

            $userDto = $this->userMapper->fromDjangoToDTO($data);
            
            $persistenceData = $this->userMapper->toPersistenceArray($userDto);
            $user = User::updateOrCreate(
                ['id_personal' => $persistenceData['id_personal']], 
                $persistenceData
            );
            $userDtoResult = $this->userMapper->toDTO($user);

            Auth::loginUsingId($userDtoResult->id);
            $this->logContext->setUsername($userDtoResult->username);

            $fullDto = $userDto->withId($userDtoResult->id);
            $this->logger()->info("Sesión local sincronizada.", [
                'data' => $fullDto
            ]);

            $this->syncLocalSession($fullDto);
            $this->clearOtherSessions($userDtoResult->id);

            $sessionData = $this->getAuthenticatedSession();

            return $this->userMapper->toDTO($sessionData);
        }, 'AuthService@authenticate');
    }

    /**
     * Invalida la sesión local y el estado del token externo.
     */
    public function logout(): void
    {
        $this->logger()->info("Iniciando cierre de sesión.");

        $this->handle(function () {
            $username = Auth::user()?->username;

            if ($username) {
                $response = $this->authClient->invalidateToken($username);
                if ($response->successful() && ($response->json()['status'] ?? '') === 'Success') {
                    $this->logger()->info("Token externo inactivado.");
                } else {
                    $this->logger()->error("Fallo al inactivar token externo.");
                }
            }

            $this->completeLogout();
        }, 'AuthService@logout');
    }

    /**
     * Recupera la información del usuario desde la sesión persistida.
     * 
     * @return UserDTO
     */
    public function checkSession(): UserDTO
    {
        $this->logger()->info("Consultando sesión activa.");
        
        return $this->handle(function () {
            $sessionData = $this->getAuthenticatedSession();
            $this->logger()->info("Sesión local recuperada.", [
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
                $this->logger()->error("Token externo inválido.", [
                    'response' => $response->json()
                ]);
            }

            return $isValid;
        }, 'AuthService@verifyToken');
    }

    private function syncLocalSession(UserDTO $dto): void
    {
        Session::put([
            'id' => $dto->id,
            'idPersonal' => $dto->idPersonal,
            'username' => $dto->username,
            'name' => $dto->name,
            'email' => $dto->email,
            'puestoActual' => $dto->puestoActual,
            'rutaFoto' => $dto->rutaFoto,
            'permisos' => $dto->permisos,
            'token' => $dto->token,
        ]);
    }

    private function clearOtherSessions(int $userId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }
        
        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->where('id', '!=', Session::getId())
            ->delete();
    }

    private function completeLogout(): void
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        if (method_exists(Auth::guard(), 'logout')) {
            Auth::logout();
        }

        if (\Illuminate\Support\Facades\Session::isStarted()) {
            \Illuminate\Support\Facades\Session::invalidate();
            \Illuminate\Support\Facades\Session::regenerateToken();
        }
    }

    private function getAuthenticatedSession(): array
    {
        if (!Auth::check() || !Session::has('username')) {
            throw new AuthenticationException('Sesión no encontrada o expirada.');
        }

        return Session::only([
            'id', 'idPersonal', 'username', 'name', 'email',
            'puestoActual', 'rutaFoto', 'permisos', 'token'
        ]);
    }
}