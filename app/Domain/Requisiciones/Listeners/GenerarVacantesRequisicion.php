<?php

namespace App\Domain\Requisiciones\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domain\Requisiciones\Models\Vacante;
use App\Domain\Requisiciones\Enums\VacanteEstado;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobada;
use App\Domain\Requisiciones\Actions\EvaluarEstadoRequisicionAction;

class GenerarVacantesRequisicion implements ShouldQueue
{
    public function __construct(
        private readonly EvaluarEstadoRequisicionAction $evaluarEstadoAction
    ) {}

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
                // Determinar el estado inicial de la vacante basado en la situación del puesto
                $puesto = $detalle->puesto;
                $estadoInicial = VacanteEstado::PENDIENTE_VINCULACION_SGC->value; // Por defecto si no tiene nada

                if ($puesto) {
                    if ($puesto->tienePerfilVinculadoSGC()) {
                        $estadoInicial = VacanteEstado::BUSQUEDA_ACTIVA->value;
                    } elseif ($puesto->tienePerfilLocalEnProceso()) {
                        $estadoInicial = VacanteEstado::PENDIENTE_PERFIL->value;
                    }
                }

                $vacantesAInsertar = [];
                $cantidad = (int) $detalle->cantidad_solicitada;
                
                for ($i = 0; $i < $cantidad; $i++) {
                    $vacantesAInsertar[] = [
                        'detalle_requisicion_id' => $detalle->id,
                        'estado' => $estadoInicial,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($vacantesAInsertar)) {
                    Vacante::insert($vacantesAInsertar);
                }
            }
        });

        // Una vez que las vacantes están físicamente creadas, evaluar el estado global de la requisición.
        $this->evaluarEstadoAction->execute($requisicion);
    }
}