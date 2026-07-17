<?php

namespace App\Domain\Puestos\Rules\Delete;

use App\Domain\Puestos\Rules\PuestoRuleInterface;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Exceptions\Domain\BusinessRuleException;

class ValidarPuestosSubordinadosRule implements PuestoRuleInterface
{
    /**
     * Valida que el puesto no tenga subordinados directos antes de eliminarlo.
     */
    public function validate(Puesto $puesto, ?PuestoDTO $dto = null): void
    {
        if ($puesto->subordinados()->exists()) {
            throw BusinessRuleException::withMessage("El puesto no puede ser eliminado porque tiene puestos subordinados directos.", "PUESTO_CON_SUBORDINADOS");
        }
    }
}