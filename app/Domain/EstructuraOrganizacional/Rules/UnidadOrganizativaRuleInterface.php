<?php

namespace App\Domain\EstructuraOrganizacional\Rules;

use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;

interface UnidadOrganizativaRuleInterface
{
    /**
     * Aplica la regla de negocio sobre la unidad organizativa.
     * 
     * @param UnidadOrganizativa $model
     * @param UnidadOrganizativaDTO|null $dto
     * @return void
     * @throws \App\Exceptions\Domain\BusinessRuleException
     */
    public function validate(UnidadOrganizativa $model, ?UnidadOrganizativaDTO $dto = null): void;
}