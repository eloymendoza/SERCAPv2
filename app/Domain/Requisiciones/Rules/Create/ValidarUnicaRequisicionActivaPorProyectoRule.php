<?php

namespace App\Domain\Requisiciones\Rules\Create;

use App\Domain\Requisiciones\Rules\SolicitudRequisicionRuleInterface;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Enums\RequisicionEstadoEnum;
use App\Exceptions\Domain\BusinessRuleException;

class ValidarUnicaRequisicionActivaPorProyectoRule implements SolicitudRequisicionRuleInterface
{
    /**
     * Valida que no exista ya una requisición activa para el mismo proyecto,
     * a menos que se trate de una enmienda.
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void
    {
        $requisicionPadreId = $dto ? $dto->requisicionPadreId : ($model ? $model->requisicion_padre_id : null);
        
        if ($requisicionPadreId) {
            return;
        }

        $proyectoId = $dto ? $dto->proyectoId : ($model ? $model->proyecto_id : null);

        if (!$proyectoId) {
            return;
        }

        $existeActiva = Requisicion::whereHas('solicitud', function ($query) use ($proyectoId, $model, $dto) {
            $query->where('proyecto_id', $proyectoId);
            
            $solicitudId = $model ? $model->id : ($dto ? $dto->id : null);
            if ($solicitudId) {
                $query->where('id', '!=', $solicitudId);
            }
        })
        ->whereNotIn('estado', [
            RequisicionEstadoEnum::CUBIERTA,
            RequisicionEstadoEnum::CANCELADA,
        ])
        ->exists();

        if ($existeActiva) {
            throw BusinessRuleException::withMessage(
                'El proyecto seleccionado ya cuenta con una requisición activa. Para realizar cambios, debe generar una modificación (enmienda) en lugar de una requisición nueva.',
                'REQUISICION_ACTIVA_EXISTENTE'
            );
        }
    }
}