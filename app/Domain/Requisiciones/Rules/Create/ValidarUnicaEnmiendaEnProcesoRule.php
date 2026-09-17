<?php

namespace App\Domain\Requisiciones\Rules\Create;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstadoEnum;
use App\Domain\Requisiciones\Rules\SolicitudRequisicionRuleInterface;

class ValidarUnicaEnmiendaEnProcesoRule implements SolicitudRequisicionRuleInterface
{
    /**
     * Previene la creación o edición de una enmienda si la requisición padre ya cuenta
     * con otra enmienda en proceso o la requisición original aún no ha sido aprobada.
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void
    {
        $requisicionPadreId = $dto ? $dto->requisicionPadreId : ($model ? $model->requisicion_padre_id : null);
        
        if (!$requisicionPadreId) {
            return;
        }

        $solicitudId = $model ? $model->id : ($dto ? $dto->id : null);

        $existeEnProceso = SolicitudRequisicion::where(function ($query) use ($requisicionPadreId) {
                $query->where('requisicion_padre_id', $requisicionPadreId)
                      ->orWhereHas('requisicion', function($q) use ($requisicionPadreId) {
                          $q->where('id', $requisicionPadreId);
                      });
            })
            ->when($solicitudId, fn($q) => $q->where('id', '!=', $solicitudId))
            ->whereIn('estado', [
                SolicitudRequisicionEstadoEnum::BORRADOR->value,
                SolicitudRequisicionEstadoEnum::EN_PROCESO->value,
                SolicitudRequisicionEstadoEnum::RECHAZADO->value,
            ])
            ->exists();

        if ($existeEnProceso) {
            throw BusinessRuleException::withMessage(
                'La requisición seleccionada ya tiene una solicitud o modificación en curso (o rechazada pendiente de corrección). No puede crear otra hasta que la actual sea terminada o cancelada.',
                'ENMIENDA_EN_PROCESO'
            );
        }
    }
}