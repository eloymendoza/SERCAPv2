<?php

namespace App\Exceptions\Domain;

/**
 * Excepción de regla de negocio genérica para retornar mensajes de validación o políticas.
 */
class BusinessRuleException extends DomainException
{
    protected string $errorCode = 'BUSINESS_RULE_VIOLATION';
    protected int $statusCode = 422;

    /**
     * Instancia la regla de negocio con un mensaje y código de error personalizado.
     */
    public static function withMessage(string $message, string $errorCode = 'BUSINESS_RULE_VIOLATION'): self
    {
        $exception = new self($message);
        $exception->errorCode = $errorCode;

        return $exception;
    }
}
