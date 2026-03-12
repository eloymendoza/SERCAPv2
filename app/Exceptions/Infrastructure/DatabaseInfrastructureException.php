<?php

namespace App\Exceptions\Infrastructure;

use Throwable;

/**
 * Representa un fallo crítico en la capa de persistencia de datos.
 */
class DatabaseInfrastructureException extends InfrastructureException
{
    protected string $errorCode = 'DATABASE_ERROR';
    protected int $statusCode = 500;

    /**
     * @param string $message Mensaje técnico o amigable sobre el fallo.
     * @param Throwable|null $previous Causa raíz técnica (ej: QueryException).
     */
    public function __construct(string $message = 'Ocurrió un problema técnico al acceder a los datos.', ?Throwable $previous = null)
    {
        parent::__construct($message, 500, 'DATABASE_ERROR', [], $previous);
    }
}
