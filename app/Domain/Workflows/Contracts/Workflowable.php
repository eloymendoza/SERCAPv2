<?php

namespace App\Domain\Workflows\Contracts;

/**
 * Contrato para modelos que pueden emitirse al flujo de trabajo (Django).
 */
interface Workflowable
{
    /**
     * Retorna el identificador local de la solicitud.
     */
    public function getIdentificador(): int;

    /**
     * Aplica la instancia de workflow generada a la solicitud local y actualiza su estado.
     */
    public function aplicarWorkflowInstancia(int $idInstanciaWorkflow): void;

    /**
     * Transiciona la solicitud directamente a un estado auto-aprobado (saltándose el workflow).
     */
    public function autoAprobar(): void;
}