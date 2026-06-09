<?php

namespace App\Domain\Workflows\DTOs;

class WorkflowInstanceDTO
{
    public function __construct(
        public readonly int $idInstancia,
        public readonly int $workflowId,
        public readonly string $estado,
        public readonly int $idPersonalEmisor,
        public readonly array $firmantes = [],
        public readonly array $historial = [],
        public readonly array $rawResponse = []
    ) {}
}