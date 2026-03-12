<?php

namespace App\Exceptions\Domain;

/**
 * Maneja errores relacionados con el proceso de autenticación.
 */
class AuthException extends DomainException
{
    protected string $errorCode = 'AUTH_INVALID_CREDENTIALS';
    protected int $statusCode = 401;

    /**
     * Crea una instancia de la excepción para credenciales no válidas.
     * 
     * @param string $message Mensaje amigable para el usuario.
     * @return self
     */
    public static function invalidCredentials(string $message = 'Las credenciales proporcionadas son incorrectas.'): self
    {
        return new self($message);
    }
}
