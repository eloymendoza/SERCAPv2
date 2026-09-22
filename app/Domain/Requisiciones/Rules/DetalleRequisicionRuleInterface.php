<?php

namespace App\Domain\Requisiciones\Rules;

use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;

interface DetalleRequisicionRuleInterface
{
    /**
     * Valida reglas de negocio para un detalle de requisición.
     *
     * @param DetalleRequisicion|null $model Instancia persistida (null si es creación).
     * @param DetalleRequisicionDTO|null $dto Datos entrantes de la petición.
     * @param int|null $requisicionId ID de la requisición padre (útil en creación).
     * @throws \App\Exceptions\Domain\BusinessRuleException
     */
    public function validate(?DetalleRequisicion $model, ?DetalleRequisicionDTO $dto = null, ?int $requisicionId = null): void;
}