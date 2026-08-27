<?php

namespace App\Domain\Autenticacion\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Auth;
use App\Domain\Autenticacion\Mappers\UserMapper;
use App\Infrastructure\Clients\DjangoAuthClient;

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
     * Verifica la validez del token contra el servicio externo.
     * 
     * @param string $username
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $username, string $token): bool
    {
        return $this->handle(function () use ($username, $token) {
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
}