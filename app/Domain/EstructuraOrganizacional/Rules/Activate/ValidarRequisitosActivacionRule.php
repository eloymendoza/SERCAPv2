<?php

namespace App\Domain\EstructuraOrganizacional\Rules\Activate;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Rules\UnidadOrganizativaRuleInterface;

class ValidarRequisitosActivacionRule implements UnidadOrganizativaRuleInterface
{
    /**
     * Valida que la unidad cuente con todos los requisitos mínimos para entrar en vigor.
     */
    public function validate(UnidadOrganizativa $model, ?UnidadOrganizativaDTO $dto = null): void
    {
        // Regla: No puede existir un área sin encargado al momento de activarse
        if (empty($model->encargado_id) && empty($model->encargado_usuario)) {
            throw new BusinessRuleException('No se puede activar la unidad organizativa. Debe tener un encargado asignado (encargado_id y encargado_usuario).');
        }
    }
}