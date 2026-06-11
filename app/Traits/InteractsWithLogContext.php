<?php

namespace App\Traits;

use App\Logging\LogContext;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;

trait InteractsWithLogContext
{
    /**
     * Define el canal de logs a utilizar.
     */
    abstract protected function getLogChannel(): string;

    /**
     * Resuelve el canal desde el LogContext actual, o utiliza el canal definido
     * en el artefacto si no hay un contexto activo (ej. comandos Artisan o Jobs).
     */
    private function resolveChannel(): string
    {
        try {
            return app(LogContext::class)->channel();
        } catch (\Throwable) {
            return $this->getLogChannel();
        }
    }

    /**
     * Expone una instancia del logger utilizando el canal resuelto dinámicamente.
     */
    protected function logger(): LoggerInterface
    {
        return Log::channel($this->resolveChannel());
    }
}