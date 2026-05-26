<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Logging\LogContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inicializa el contexto de traza para la petición actual.
 *
 * Se ejecuta al inicio del pipeline HTTP para asegurar que todas las capas
 * subsecuentes tengan acceso al request_id.
 */
class InitializeLogContext
{
    public function __construct(private readonly LogContext $logContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        // Inicializamos con el canal 'app' como fallback por si no hay un
        // controller definido que sobrescriba este valor.
        $this->logContext->initialize($request, $requestId, 'app');

        /** @var Response $response */
        $response = $next($request);

        // Opcional: incluir el ID en la respuesta para facilitar depuración en cliente.
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
