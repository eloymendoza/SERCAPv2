<?php

namespace App\Domain\Requisiciones\Rules;

use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;

interface SolicitudRequisicionRuleInterface
{
    /**
     * Evalúa invariantes de negocio para una SolicitudRequisicion.
     * 
     * @param SolicitudRequisicion|null $model Instancia persistida (null si es creación)
     * @param SolicitudRequisicionDTO|null $dto DTO con la nueva información a aplicar
     * @throws \App\Exceptions\Domain\BusinessRuleException
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void;
}