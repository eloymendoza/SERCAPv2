<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\AuthDTO;
use App\DTOs\UserDTO;
use App\Mappers\UserMapper;
use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use App\Clients\DjangoAuthClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Exceptions\Domain\AuthException;
use Illuminate\Auth\AuthenticationException;

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
        Log::channel($this->logContext->channel())->info("Iniciando autenticación.", [
            'username' => $dto->username
        ]);
        
        return $this->handle(function () use ($dto) {
            $response = $this->authClient->authenticate($dto->username, $dto->password);

            if (!$response->successful()) {
                throw AuthException::invalidCredentials('Credenciales inválidas o error de conexión.');
            }

            $data = $response->json();
            Log::channel($this->logContext->channel())->info("Autenticación exitosa.", [
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
            Log::channel($this->logContext->channel())->info("Sesión local sincronizada.", [
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
        Log::channel($this->logContext->channel())->info("Iniciando cierre de sesión.");

        $this->handle(function () {
            $username = Auth::user()?->username;

            if ($username) {
                $response = $this->authClient->invalidateToken($username);
                if ($response->successful() && ($response->json()['status'] ?? '') === 'Success') {
                    Log::channel($this->logContext->channel())->info("Token externo inactivado.");
                } else {
                    Log::channel($this->logContext->channel())->error("Fallo al inactivar token externo.");
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
        Log::channel($this->logContext->channel())->info("Consultando sesión activa.");
        
        return $this->handle(function () {
            $sessionData = $this->getAuthenticatedSession();
            Log::channel($this->logContext->channel())->info("Sesión local recuperada.", [
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
                Log::channel($this->logContext->channel())->error("Token externo inválido.", [
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
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
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