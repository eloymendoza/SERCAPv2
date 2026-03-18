<?php

namespace App\Exceptions\Domain;

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
        return new self($message);
    }
}