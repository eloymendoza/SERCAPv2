<?php

namespace App\Exceptions\Domain;

use App\Exceptions\BaseApiException;

/**
 * Clase base para errores de lógica de negocio o validación.
 * 
 * Estas excepciones NO se registran en los logs de error por defecto
 * para evitar saturar el sistema con errores esperados del usuario.
 */
abstract class DomainException extends BaseApiException
{
    /**
     * Evita que la excepción sea reportada en los logs de Laravel.
     * 
     * @return bool
     */
    public function report(): bool
    {
        return false;
    }
}
