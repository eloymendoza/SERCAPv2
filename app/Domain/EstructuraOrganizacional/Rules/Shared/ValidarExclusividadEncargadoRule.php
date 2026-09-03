<?php

namespace App\Domain\EstructuraOrganizacional\Rules\Shared;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;
use App\Domain\EstructuraOrganizacional\Rules\UnidadOrganizativaRuleInterface;

class ValidarExclusividadEncargadoRule implements UnidadOrganizativaRuleInterface
{
    /**
     * Valida que un empleado no sea encargado de más de una unidad activa a la vez.
     */
    public function validate(UnidadOrganizativa $model, ?UnidadOrganizativaDTO $dto = null): void
    {
        $isActivating = $dto === null;

        // Si estamos editando un borrador, permitimos asignar al jefe libremente,
        // ya que la regla de exclusividad solo importa cuando el área se vuelve oficial (Activa).
        if (!$isActivating && strtolower($model->estado) !== UnidadOrganizativaEstadoEnum::ACTIVO->value) {
            return;
        }

        $encargadoId = $dto ? $dto->encargadoId : $model->encargado_id;
        $encargadoUsuario = $dto ? $dto->encargadoUsuario : $model->encargado_usuario;

        if (empty($encargadoId) && empty($encargadoUsuario)) {
            return;
        }

        $query = UnidadOrganizativa::where('estado', UnidadOrganizativaEstadoEnum::ACTIVO->value)
            ->where(function($q) use ($encargadoId, $encargadoUsuario) {
                if ($encargadoId && $encargadoUsuario) {
                    $q->where('encargado_id', $encargadoId)->orWhere('encargado_usuario', $encargadoUsuario);
                } elseif ($encargadoId) {
                    $q->where('encargado_id', $encargadoId);
                } else {
                    $q->where('encargado_usuario', $encargadoUsuario);
                }
            })
            ->where('id', '!=', $model->id);
            
        // Si estamos activando un reemplazo, ignoramos a la unidad que va a morir hoy.
        if ($model->reemplaza_a_id) {
            $query->where('id', '!=', $model->reemplaza_a_id);
        }

        if ($query->exists()) {
            throw new BusinessRuleException('El empleado seleccionado ya es jefe de otra unidad organizativa activa. Un empleado solo puede gestionar una unidad a la vez.');
        }
    }
}