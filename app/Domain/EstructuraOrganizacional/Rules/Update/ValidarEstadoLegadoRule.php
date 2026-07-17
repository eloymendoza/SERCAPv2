<?php

namespace App\Domain\EstructuraOrganizacional\Rules\Update;

use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;
use App\Domain\EstructuraOrganizacional\Rules\UnidadOrganizativaRuleInterface;
use App\Exceptions\Domain\BusinessRuleException;

class ValidarEstadoLegadoRule implements UnidadOrganizativaRuleInterface
{
    /**
     * Valida que no se editen unidades organizativas con estado legado.
     */
    public function validate(UnidadOrganizativa $model, ?UnidadOrganizativaDTO $dto = null): void
    {
        if ($model->estado === UnidadOrganizativaEstadoEnum::LEGADO->value) {
            throw BusinessRuleException::withMessage("Las unidades organizativas con estado legado no pueden ser editadas.", "UNIDAD_LEGADO_NO_EDITABLE");
        }
    }
}