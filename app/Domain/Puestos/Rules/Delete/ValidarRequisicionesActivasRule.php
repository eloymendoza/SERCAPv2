<?php

namespace App\Domain\Puestos\Rules\Delete;

use App\Domain\Puestos\Rules\PuestoRuleInterface;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Exceptions\Domain\BusinessRuleException;

class ValidarRequisicionesActivasRule implements PuestoRuleInterface
{
    /**
     * Valida que el puesto no tenga requisiciones activas antes de eliminarlo.
     */
    public function validate(Puesto $puesto, ?PuestoDTO $dto = null): void
    {
        if ($puesto->tieneRequisicionesActivas()) {
            throw BusinessRuleException::withMessage("No es posible eliminar el puesto porque existen procesos de selección activos vinculados a él.", "PUESTO_CON_REQUISICIONES_ACTIVAS");
        }
    }
}
