<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @property int $statusCode Código de estado HTTP (ej: 401, 500).
 * @property string $errorCode Código de error interno para el frontend (ej: 'AUTH_INVALID_CREDENTIALS').
 * @property array $details Información adicional sobre el error.
 */
abstract class BaseApiException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorCode = 'INTERNAL_ERROR';
    protected array $details = [];

    /**
     * @param string $message Mensaje de error amigable.
     * @param int|null $statusCode Código HTTP.
     * @param string|null $errorCode Identificador de error único.
     * @param array $details Metadatos adicionales.
     * @param Throwable|null $previous Excepción previa para encadenamiento.
     */
    public function __construct(
        string $message = "Ocurrió un error inesperado.",
        ?int $statusCode = null,
        ?string $errorCode = null,
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->statusCode = $statusCode ?? $this->statusCode;
        $this->errorCode = $errorCode ?? $this->errorCode;
        $this->details = $details;
    }

    /**
     * Estandariza la respuesta JSON ante cualquier excepción.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code'    => $this->errorCode,
                'message' => $this->getMessage(),
                'details' => $this->details
            ]
        ], $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
