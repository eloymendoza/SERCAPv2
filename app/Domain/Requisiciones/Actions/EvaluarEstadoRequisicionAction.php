<?php

namespace App\Domain\Requisiciones\Actions;

use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Models\Postulacion;
use App\Domain\Requisiciones\Enums\RequisicionEstadoEnum;
use App\Domain\Requisiciones\Enums\VacanteEstadoEnum;
use App\Domain\Requisiciones\Enums\PostulacionEstadoEnum;

class EvaluarEstadoRequisicionAction
{
    /**
     * Evalúa y actualiza el estado de la Requisición basándose en 
     * el estado actual de sus vacantes y postulaciones, siguiendo 
     * la máquina de estados definida en RequisicionEstadoEnum.
     */
    public function execute(Requisicion $requisicion): void
    {
        $vacantes = Vacante::whereHas('detalleRequisicion', function ($query) use ($requisicion) {
            $query->where('requisicion_id', $requisicion->id);
        })->get();

        if ($vacantes->isEmpty()) {
            return;
        }

        $totalVacantes = $vacantes->count();
        $detallesIds = $vacantes->pluck('detalle_requisicion_id')->unique();

        // Contadores de Vacantes (Se usan las instancias del Enum por el Attribute Cast del modelo)
        $canceladas = $vacantes->where('estado', VacanteEstadoEnum::CANCELADA)->count();
        $contratadas = $vacantes->where('estado', VacanteEstadoEnum::CONTRATADA)->count();
        $enAuditoria = $vacantes->where('estado', VacanteEstadoEnum::EN_AUDITORIA)->count();
        $seleccionadas = $vacantes->where('estado', VacanteEstadoEnum::SELECCIONADA)->count();
        $busquedaActiva = $vacantes->where('estado', VacanteEstadoEnum::BUSQUEDA_ACTIVA)->count();
        
        $pendientesPerfil = $vacantes->whereIn('estado', [
            VacanteEstadoEnum::PENDIENTE_PERFIL, 
            VacanteEstadoEnum::PENDIENTE_VINCULACION_SGC
        ])->count();

        // Contadores de Postulaciones activas compitiendo o avanzando
        $postulacionesActivas = Postulacion::whereIn('detalle_requisicion_id', $detallesIds)
            ->whereIn('estado', [
                PostulacionEstadoEnum::PRESELECCIONADO->value,
                PostulacionEstadoEnum::EN_ENTREVISTA_TECNICA->value,
                PostulacionEstadoEnum::EN_EXAMENES->value,
                PostulacionEstadoEnum::AUTORIZACION_EXCEPCIONAL->value,
                PostulacionEstadoEnum::SELECCIONADO->value
            ])->count();

        // --- Máquina de Estados (Evaluación Jerárquica Bottom-Up) ---
        $nuevoEstado = match (true) {
            $canceladas === $totalVacantes => RequisicionEstadoEnum::CANCELADA,
            ($contratadas + $canceladas) === $totalVacantes => RequisicionEstadoEnum::CUBIERTA,
            ($seleccionadas + $enAuditoria + $contratadas + $canceladas) === $totalVacantes && $enAuditoria > 0 => RequisicionEstadoEnum::VALIDACION_ADMINISTRATIVA,
            $contratadas > 0 => RequisicionEstadoEnum::CIERRE_PARCIAL,
            $postulacionesActivas > 0 || $seleccionadas > 0 || $enAuditoria > 0 => RequisicionEstadoEnum::EN_PROCESO,
            $busquedaActiva > 0 => RequisicionEstadoEnum::ABIERTA,
            $pendientesPerfil === $totalVacantes => RequisicionEstadoEnum::PENDIENTE_PERFIL,
            default => RequisicionEstadoEnum::BORRADOR,
        };

        // Actualizar el estado si hubo un cambio
        if ($requisicion->estado !== $nuevoEstado) {
            $requisicion->update([
                'estado' => $nuevoEstado
            ]);
        }
    }
}