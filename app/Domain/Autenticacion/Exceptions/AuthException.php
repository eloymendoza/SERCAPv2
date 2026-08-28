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
        return new self($message);
    }

    /**
     * @param string $message
     * @return self
     */
    public static function accountLocked(string $message = 'La cuenta se encuentra bloqueada por seguridad.'): self
    {
        $e = new self($message);
        $e->errorCode = 'AUTH_ACCOUNT_LOCKED';
        $e->statusCode = 403;
        
        return $e;
    }

    /**
     * @param string $message
     * @return self
     */
    public static function accessDenied(string $message = 'El usuario no tiene privilegios para ingresar al sistema.'): self
    {
        $e = new self($message);
        $e->errorCode = 'AUTH_ACCESS_DENIED';
        $e->statusCode = 403;
        
        return $e;
    }
}