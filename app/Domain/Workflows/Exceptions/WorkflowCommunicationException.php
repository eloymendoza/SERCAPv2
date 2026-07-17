<?php

namespace App\Domain\Workflows\Exceptions;

use App\Exceptions\Infrastructure\InfrastructureException;

/**
 * Excepción técnica cuando falla la integración o comunicación con el motor de Workflows.
 */
class WorkflowCommunicationException extends InfrastructureException
{
    protected string $errorCode = 'WORKFLOW_COMMUNICATION_ERROR';
    protected int $statusCode = 502; // Bad Gateway
}