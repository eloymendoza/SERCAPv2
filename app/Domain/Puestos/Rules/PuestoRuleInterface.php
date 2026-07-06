<?php

namespace App\Domain\Puestos\Rules;

use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;

interface PuestoRuleInterface
{
    /**
     * Valida una regla de negocio sobre un puesto.
     * Lanza una excepción si la validación falla.
     */
    public function validate(Puesto $puesto, ?PuestoDTO $dto = null): void;
}
