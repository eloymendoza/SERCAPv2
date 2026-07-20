<?php

namespace App\Domain\Requisiciones\Listeners;

use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\Enums\VacanteEstadoEnum;
use App\Domain\Puestos\Events\PerfilPuestoVinculadoSGC;
use App\Domain\Requisiciones\Actions\EvaluarEstadoRequisicionAction;

class LiberarVacantesVinculadasListener
{
    public function __construct(
        private readonly EvaluarEstadoRequisicionAction $evaluarEstadoAction
    ) {}
    /**
     * Intercepta la vinculación de un puesto al SGC para liberar
     * las vacantes que se encontraban bloqueadas.
     */
    public function handle(PerfilPuestoVinculadoSGC $event): void
    {
        $puestoId = $event->perfilPuesto->puesto_id;

        // Obtenemos primero las vacantes afectadas para conocer a qué requisiciones pertenecen
        $vacantesAfectadas = Vacante::with('detalleRequisicion')
            ->whereHas('detalleRequisicion', function ($query) use ($puestoId) {
                $query->where('puesto_id', $puestoId);
            })
            ->whereIn('estado', [
                VacanteEstadoEnum::PENDIENTE_PERFIL->value, 
                VacanteEstadoEnum::PENDIENTE_VINCULACION_SGC->value
            ])
            ->get();

        if ($vacantesAfectadas->isEmpty()) {
            return;
        }

        // Actualizamos físicamente el estatus de las vacantes
        Vacante::whereIn('id', $vacantesAfectadas->pluck('id'))
            ->update([
                'estado' => VacanteEstadoEnum::BUSQUEDA_ACTIVA->value,
                'updated_at' => now()
            ]);

        // Obtenemos los IDs únicos de las requisiciones afectadas
        $requisicionesIds = $vacantesAfectadas->pluck('detalleRequisicion.requisicion_id')->unique();

        // Reevaluamos el estado global de cada requisición padre
        $requisiciones = Requisicion::whereIn('id', $requisicionesIds)->get();
        foreach ($requisiciones as $requisicion) {
            $this->evaluarEstadoAction->execute($requisicion);
        }
    }
}