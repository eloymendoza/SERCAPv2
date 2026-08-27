<?php

namespace App\Domain\Autenticacion\Exceptions;

use App\Exceptions\Domain\DomainException;

/**
 * Excepción de dominio para errores de autenticación.
 */
class AuthException extends DomainException
{
    protected string $errorCode = 'AUTH_INVALID_CREDENTIALS';
    protected int $statusCode = 401;

    /**
     * @param string $message
     * @return self
     */
    public static function invalidCredentials(string $message = 'Las credenciales proporcionadas son incorrectas.'): self
    {
        return new self(
            message: $message,
            statusCode: 401,
            errorCode: 'AUTH_INVALID_CREDENTIALS'
        );
    }

    /**
     * @param string $message
     * @return self
     */
    public static function accessDenied(string $message = 'El usuario no tiene permisos para acceder al sistema.'): self
    {
        return new self(
            message: $message,
            statusCode: 403,
            errorCode: 'AUTH_ACCESS_DENIED'
        );
    }

    /**
     * Report the exception.
     *
     * @return bool
     */
    public function report(): bool
    {
        \Illuminate\Support\Facades\Log::channel('auth')->error($this->getMessage(), [
            'error_code' => $this->errorCode,
            'exception' => $this
        ]);

        return false; // Evita que Laravel lo envíe también a laravel.log
    }
}