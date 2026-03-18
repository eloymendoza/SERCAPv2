<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Domain\AuthException;
use App\Actions\Auth\CompleteLogoutAction;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para la verificación de tokens de sesión contra la API de Django.
 * 
 * Este middleware actúa como una segunda capa de seguridad. Una vez que el
 * middleware `auth:sanctum` valida la sesión local de Laravel, este middleware
 * verifica de forma síncrona que el token asociado siga siendo válido en la API
 * principal de Django.
 */
class VerifyDjangoToken
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CompleteLogoutAction $completeLogoutAction
    ) {}

    /**
     * Intercepta la petición entrante para validar el estado del token.
     *
     * @param  \Illuminate\Http\Request  $request Petición HTTP actual.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next Siguiente middleware en la cadena.
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \App\Exceptions\Domain\AuthException Si el token ha expirado o es inválido en Django.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $username = Auth::user()?->username;
            
            if ($username) {
                if (!$this->authService->verifyToken($username)) {
                    Log::channel('auth')->warning("Middleware: Sesión invalidada por token expirado/inválido: {$username}");

                    $this->completeLogoutAction->execute($request);
                    
                    throw AuthException::invalidCredentials('Tu sesión ha expirado.');
                }
            }
        }
        return $next($request);
    }
}
