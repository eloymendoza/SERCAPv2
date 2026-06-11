<?php

namespace App\Logging;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Almacena y propaga el contexto de traza para una petición HTTP.
 *
 * Registrado como `scoped` en el contenedor para garantizar un ciclo de vida
 * acotado al request: compatible con PHP-FPM y con Laravel Octane.
 *
 * Flujo de uso:
 *   1. InitializeLogContext (middleware) llama a initialize() con el request_id.
 *   2. El Controller llama a setChannel() para fijar el dominio de log.
 *   3. Cualquier capa downstream (Service, Action, Client) lee channel() y baseContext()
 *      sin necesidad de conocer ni redeclarar el canal.
 */
class LogContext
{
    private string  $requestId = '';
    private string  $channel   = 'app';
    private ?string $username  = null;
    private string  $method    = '';
    private string  $path      = '';

    /** Inicializa el contexto al comienzo del request HTTP. */
    public function initialize(Request $request, string $requestId, string $defaultChannel = 'app'): void
    {
        $this->requestId = $requestId;
        $this->channel   = $defaultChannel;
        $this->method    = $request->method();
        $this->path      = $request->path();
    }

    /** Inicializa el contexto para procesos asíncronos (ej. colas) donde no hay Request. */
    public function initializeAsync(string $requestId, string $channel, string $jobName): void
    {
        $this->requestId = $requestId;
        $this->channel   = $channel;
        $this->method    = 'QUEUE';
        $this->path      = $jobName;
    }

    /**
     * Fija el canal de log del dominio actual.
     * Llamar exclusivamente desde el constructor del Controller propietario del flujo.
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * Registra el usuario autenticado en el contexto.
     * Llamar en el momento en que el usuario queda identificado dentro del flujo.
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    /**
     * Retorna el array de contexto base que se adjunta a cada entrada de log.
     *
     * @return array{request_id: string, user: string, method: string, path: string}
     */
    public function baseContext(): array
    {
        $user = $this->username;
        
        if (!$user && Auth::check()) {
            $user = Auth::user()?->username;
        }

        return [
            'request_id' => $this->requestId,
            'user'       => $user ?? 'guest',
            'method'     => $this->method,
            'path'       => $this->path,
        ];
    }

    /** Indica si el contexto ha sido inicializado por el middleware HTTP. */
    public function isInitialized(): bool
    {
        return $this->requestId !== '';
    }
}