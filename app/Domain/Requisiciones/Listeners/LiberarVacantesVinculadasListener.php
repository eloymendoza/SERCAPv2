<?php

namespace App\Domain\Requisiciones\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Enums\VacanteEstado;
use App\Domain\Puestos\Events\PerfilPuestoVinculadoSGC;

class LiberarVacantesVinculadasListener implements ShouldQueue
{
    /**
     * Intercepta la vinculación de un puesto al SGC para liberar
     * las vacantes que se encontraban bloqueadas.
     */
    public function handle(PerfilPuestoVinculadoSGC $event): void
    {
        $puestoId = $event->perfilPuesto->puesto_id;

        Vacante::whereHas('detalleRequisicion', function ($query) use ($puestoId) {
            $query->where('puesto_id', $puestoId);
        })
        ->whereIn('estado', [
            VacanteEstado::PENDIENTE_PERFIL->value, 
            VacanteEstado::PENDIENTE_VINCULACION_SGC->value
        ])
        ->update([
            'estado' => VacanteEstado::BUSQUEDA_ACTIVA->value,
            'updated_at' => now()
        ]);
    }
}