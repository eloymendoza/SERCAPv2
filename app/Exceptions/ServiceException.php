<?php

namespace App\Exceptions;

use Throwable;

/**
 * Excepción genérica de "todo terreno" para servicios y lógica de negocio.
 */
class ServiceException extends BaseApiException
{
    /**
     * @param string $message Mensaje de error para el usuario.
     * @param int|null $statusCode Status HTTP (ej: 400).
     * @param string|null $errorCode Código corto interno.
     * @param array $data Metadatos adicionales (ej: errores de validación).
     * @param Throwable|null $previous Excepción previa.
     */
    public function __construct(
        string $message = "Ocurrió un error inesperado al procesar la solicitud.",
        ?int $statusCode = null,
        ?string $errorCode = null,
        array $data = [],
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $statusCode ?? 500,
            $errorCode ?? 'SERVICE_ERROR',
            $data,
            $previous
        );
    }
}