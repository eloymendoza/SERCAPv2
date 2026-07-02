<?php

namespace App\Domain\Requisiciones\Actions;

use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Models\Postulacion;
use App\Domain\Requisiciones\Enums\RequisicionEstado;
use App\Domain\Requisiciones\Enums\VacanteEstado;
use App\Domain\Requisiciones\Enums\PostulacionEstado;

class EvaluarEstadoRequisicionAction
{
    /**
     * Evalúa y actualiza el estado de la Requisición basándose en 
     * el estado actual de sus vacantes y postulaciones, siguiendo 
     * la máquina de estados definida en RequisicionEstado.
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
        $canceladas = $vacantes->where('estado', VacanteEstado::CANCELADA)->count();
        $contratadas = $vacantes->where('estado', VacanteEstado::CONTRATADA)->count();
        $enAuditoria = $vacantes->where('estado', VacanteEstado::EN_AUDITORIA)->count();
        $seleccionadas = $vacantes->where('estado', VacanteEstado::SELECCIONADA)->count();
        $busquedaActiva = $vacantes->where('estado', VacanteEstado::BUSQUEDA_ACTIVA)->count();
        
        $pendientesPerfil = $vacantes->whereIn('estado', [
            VacanteEstado::PENDIENTE_PERFIL, 
            VacanteEstado::PENDIENTE_VINCULACION_SGC
        ])->count();

        // Contadores de Postulaciones activas compitiendo o avanzando
        $postulacionesActivas = Postulacion::whereIn('detalle_requisicion_id', $detallesIds)
            ->whereIn('estado', [
                PostulacionEstado::PRESELECCIONADO->value,
                PostulacionEstado::EN_ENTREVISTA_TECNICA->value,
                PostulacionEstado::EN_EXAMENES->value,
                PostulacionEstado::AUTORIZACION_EXCEPCIONAL->value,
                PostulacionEstado::SELECCIONADO->value
            ])->count();

        // --- Máquina de Estados (Evaluación Jerárquica Bottom-Up) ---
        $nuevoEstado = match (true) {
            $canceladas === $totalVacantes => RequisicionEstado::CANCELADA,
            ($contratadas + $canceladas) === $totalVacantes => RequisicionEstado::CUBIERTA,
            ($seleccionadas + $enAuditoria + $contratadas + $canceladas) === $totalVacantes && $enAuditoria > 0 => RequisicionEstado::VALIDACION_ADMINISTRATIVA,
            $contratadas > 0 => RequisicionEstado::CIERRE_PARCIAL,
            $postulacionesActivas > 0 || $seleccionadas > 0 || $enAuditoria > 0 => RequisicionEstado::EN_PROCESO,
            $busquedaActiva > 0 => RequisicionEstado::ABIERTA,
            $pendientesPerfil === $totalVacantes => RequisicionEstado::PENDIENTE_PERFIL,
            default => RequisicionEstado::BORRADOR,
        };

        // Actualizar el estado si hubo un cambio
        if ($requisicion->estado !== $nuevoEstado) {
            $requisicion->update([
                'estado' => $nuevoEstado
            ]);
        }
    }
}