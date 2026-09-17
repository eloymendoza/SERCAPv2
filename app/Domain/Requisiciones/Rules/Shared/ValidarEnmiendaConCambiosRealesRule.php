<?php

namespace App\Domain\Requisiciones\Rules\Shared;

use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Rules\SolicitudRequisicionRuleInterface;

class ValidarEnmiendaConCambiosRealesRule implements SolicitudRequisicionRuleInterface
{
    /**
     * Valida que una solicitud de enmienda contenga cambios reales 
     * en cantidades o sueldos respecto a la requisición original.
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void
    {
        $requisicionPadreId = $dto ? $dto->requisicionPadreId : ($model ? $model->requisicion_padre_id : null);
        
        if (!$requisicionPadreId || !$dto || !$dto->requisicion || empty($dto->requisicion->detalles)) {
            return;
        }

        $requisicionPadre = Requisicion::with('detalles')->find($requisicionPadreId);
        if (!$requisicionPadre) {
            return;
        }

        $detallesActuales = $requisicionPadre->detalles->keyBy('puesto_id');
        $huboCambio = false;
        
        $detallesNuevos = $dto->requisicion->detalles;

        foreach ($detallesNuevos as $detalleDto) {
            $puestoId = $detalleDto->puestoId;
            
            // Puestos nuevos o propuestos son cambios estructurales
            if (!$puestoId || !$detallesActuales->has($puestoId)) {
                $huboCambio = true;
                break;
            }

            $detalleOriginal = $detallesActuales->get($puestoId);
            $nuevaCantidad = (int) $detalleDto->cantidadSolicitada;
            $nuevoSueldo = (float) $detalleDto->sueldoAsignado;

            if ($nuevaCantidad !== (int) $detalleOriginal->cantidad_solicitada) {
                $huboCambio = true;
                break;
            }

            if ($nuevoSueldo !== (float) $detalleOriginal->sueldo_asignado) {
                $huboCambio = true;
                break;
            }
        }

        // Si la cantidad de elementos en el array difiere, hubo cambios
        if (count($detallesNuevos) !== $detallesActuales->count()) {
            $huboCambio = true;
        }

        if (!$huboCambio) {
            throw BusinessRuleException::withMessage(
                'La solicitud de enmienda no contiene modificaciones reales en las cantidades o sueldos respecto a la requisición activa original.',
                'ENMIENDA_SIN_CAMBIOS'
            );
        }
    }
}