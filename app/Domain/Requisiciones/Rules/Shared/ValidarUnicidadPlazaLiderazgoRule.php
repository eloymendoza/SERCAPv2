<?php

namespace App\Domain\Requisiciones\Rules\Shared;

use App\Domain\Puestos\Models\Puesto;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use App\Domain\Requisiciones\Enums\RequisicionEstadoEnum;
use App\Domain\Requisiciones\Rules\DetalleRequisicionRuleInterface;

class ValidarUnicidadPlazaLiderazgoRule implements DetalleRequisicionRuleInterface
{
    /**
     * Valida que no se soliciten múltiples plazas para puestos de liderazgo en un mismo proyecto.
     */
    public function validate(?DetalleRequisicion $model, ?DetalleRequisicionDTO $dto = null, ?int $requisicionId = null): void
    {
        $puestoId = $dto ? $dto->puestoId : ($model ? $model->puesto_id : null);

        if (!$puestoId) {
            return;
        }

        $puesto = Puesto::find($puestoId);
        
        // Verificamos si el puesto tiene subordinados directos
        if (!$puesto || !$puesto->subordinados()->exists()) {
            return; // Si no es puesto de liderazgo, no aplica la restricción
        }

        $cantidadSolicitada = $dto ? $dto->cantidadSolicitada : ($model ? $model->cantidad_solicitada : 1);

        if ($cantidadSolicitada > 1) {
            throw BusinessRuleException::withMessage(
                "No se puede solicitar más de 1 vacante simultánea para un puesto de liderazgo.",
                "CANTIDAD_LIDERAZGO_EXCEDIDA"
            );
        }

        // Obtener el ID de la requisición y el proyecto actual
        if ($model) {
            $reqId = $model->requisicion_id;
        } else {
            $reqId = $requisicionId;
        }
        
        if (!$reqId) {
            return;
        }

        $requisicionActual = Requisicion::with('solicitud')->find($reqId);
        $proyectoActualId = $requisicionActual?->solicitud?->proyecto_id;

        // Buscar otras plazas solicitadas (o activas) para este mismo puesto, excluyendo canceladas
        $query = DetalleRequisicion::where('puesto_id', $puestoId)
            ->whereHas('requisicion', function($q) {
                $q->where('estado', '!=', RequisicionEstadoEnum::CANCELADA->value);
            });

        if ($model) {
            $query->where('id', '!=', $model->id);
        }

        $detallesExistentes = $query->with('requisicion.solicitud')->get();

        foreach ($detallesExistentes as $detalleExistente) {
            $proyectoExistenteId = $detalleExistente->requisicion?->solicitud?->proyecto_id;
            
            if ($proyectoExistenteId === $proyectoActualId) {
                throw BusinessRuleException::withMessage(
                    "El puesto de liderazgo seleccionado ya cuenta con una plaza activa (en reclutamiento u ocupada) para este mismo proyecto. El sistema solo permite una plaza de liderazgo por proyecto para evitar conflictos jerárquicos.",
                    "LIDERAZGO_DUPLICADO_PROYECTO"
                );
            }
        }
    }
}