<?php

namespace App\Domain\Requisiciones\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Enums\VacanteEstado;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobada;

class GenerarVacantesRequisicion implements ShouldQueue
{
    /**
     * Intercepta el evento de aprobación de la solicitud de requisición
     * para generar físicamente las vacantes solicitadas en base a las partidas.
     */
    public function handle(SolicitudRequisicionAprobada $event): void
    {
        $solicitud = $event->solicitud;
        
        // Garantizar que la relación se encuentra cargada para evitar N+1
        $solicitud->loadMissing('requisicion.detalles');

        $requisicion = $solicitud->requisicion;

        if (!$requisicion || $requisicion->detalles->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($requisicion) {
            foreach ($requisicion->detalles as $detalle) {
                $vacantesAInsertar = [];
                $cantidad = (int) $detalle->cantidad_solicitada;
                
                for ($i = 0; $i < $cantidad; $i++) {
                    $vacantesAInsertar[] = [
                        'detalle_requisicion_id' => $detalle->id,
                        'estado' => VacanteEstado::PENDIENTE_PERFIL->value, // Estado inicial por defecto
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($vacantesAInsertar)) {
                    Vacante::insert($vacantesAInsertar);
                }
            }
        });
    }
}