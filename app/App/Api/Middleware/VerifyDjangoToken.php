<?php

namespace App\App\Api\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Domain\Autenticacion\Services\AuthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Domain\Autenticacion\Exceptions\AuthException;
use App\Logging\LogContext;
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
        private readonly LogContext $logContext
    ) {}

    /**
     * Intercepta la petición entrante para validar el estado del token.
     *
     * @param  \Illuminate\Http\Request  $request Petición HTTP actual.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next Siguiente middleware en la cadena.
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \App\Domain\Autenticacion\Exceptions\AuthException Si el token ha expirado o es inválido en Django.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $username = Auth::user()?->username;
            
            if ($username) {
                if (!$this->authService->verifyToken($username)) {
                    Log::channel($this->logContext->channel())->warning("Middleware: Sesión invalidada por token expirado/inválido: {$username}");

                    $user = Auth::user();
                    if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                        $user->currentAccessToken()->delete();
                    }

                    if (method_exists(Auth::guard(), 'logout')) {
                        Auth::logout();
                    }

                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    }
                    
                    throw AuthException::invalidCredentials('Tu sesión ha expirado.');
                }
            }
        }
        return $next($request);
    }
}