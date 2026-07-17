<?php

namespace App\Domain\Puestos\Rules\Update;

use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Domain\Puestos\Enums\PuestoEstadoEnum;
use App\Domain\Puestos\Rules\PuestoRuleInterface;
use App\Exceptions\Domain\BusinessRuleException;

class ValidarEstadoLegadoRule implements PuestoRuleInterface
{
    /**
     * Valida que no se editen puestos con estado legado.
     */
    public function validate(Puesto $puesto, ?PuestoDTO $dto = null): void
    {
        if ($puesto->estado === PuestoEstadoEnum::LEGADO->value) {
            throw BusinessRuleException::withMessage("Los puestos con estado legado no pueden ser editados.", "PUESTO_LEGADO_NO_EDITABLE");
        }
    }
}