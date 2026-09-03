<?php

namespace App\Domain\EstructuraOrganizacional\Rules\Update;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;
use App\Domain\EstructuraOrganizacional\Rules\UnidadOrganizativaRuleInterface;

class BloquearEdicionEstructuralRule implements UnidadOrganizativaRuleInterface
{
    /**
     * Valida que no se intente modificar campos estructurales si la unidad está activa.
     */
    public function validate(UnidadOrganizativa $model, ?UnidadOrganizativaDTO $dto = null): void
    {
        if (!$dto) return;

        if (strtolower($model->estado) !== UnidadOrganizativaEstadoEnum::ACTIVO->value) {
            return;
        }

        // Si el estado es activo, está estrictamente prohibido alterar nombre o nivel
        if ($dto->nivel !== null && $dto->nivel !== $model->nivel) {
            throw new BusinessRuleException('No se puede modificar el nivel de una unidad activa. Debe crear un reemplazo para reestructurar.');
        }

        if ($dto->nombre !== null && $dto->nombre !== $model->nombre) {
            throw new BusinessRuleException('No se puede modificar el nombre de una unidad activa. Debe crear un reemplazo para reestructurar.');
        }
    }
}