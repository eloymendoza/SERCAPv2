<?php

namespace App\Domain\Requisiciones\Listeners;

use Illuminate\Support\Facades\DB;
use App\Domain\Requisiciones\Models\Puesto;
use App\Domain\Requisiciones\Models\PerfilPuesto;
use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobada;

class ProcesarPuestosPropuestosListener
{
    /**
     * Intercepta la aprobación de la requisición para convertir las propuestas de puestos en reales.
     */
    public function handle(SolicitudRequisicionAprobada $event): void
    {
        $solicitud = $event->solicitud;
        $solicitud->loadMissing('requisicion.detalles.propuestaPuesto');

        $requisicion = $solicitud->requisicion;

        if (!$requisicion || $requisicion->detalles->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($solicitud, $requisicion) {
            foreach ($requisicion->detalles as $detalle) {
                if ($detalle->propuestaPuesto) {
                    $propuesta = $detalle->propuestaPuesto;

                    // 1. Crear el Puesto maestro
                    $puestoNuevo = Puesto::create([
                        'nombre_puesto' => $propuesta->nombre_puesto,
                        'direccion_id' => $solicitud->direccion_id,
                        'reporta_a_puesto_id' => $propuesta->reporta_a_puesto_id,
                        'tipo' => $propuesta->tipo,
                    ]);

                    // 2. Crear la SolicitudPerfilPuesto automáticamente (en borrador)
                    $solicitudPerfil = SolicitudPerfilPuesto::create([
                        'solicitante_id' => $solicitud->elaborador_id ?? $solicitud->solicitante_id,
                        'direccion_id' => $solicitud->direccion_id,
                        'estado' => 'borrador', 
                        'observaciones' => 'Solicitud autogenerada desde la requisición de personal ' . $solicitud->folio,
                    ]);

                    // 3. Ligar el perfil vacío para el nuevo puesto
                    PerfilPuesto::create([
                        'solicitud_id' => $solicitudPerfil->id,
                        'puesto_id' => $puestoNuevo->id,
                    ]);

                    // 4. Ligar el detalle con el nuevo puesto real
                    $detalle->update(['puesto_id' => $puestoNuevo->id]);

                    // 5. Eliminar la propuesta
                    $propuesta->delete();
                }
            }
        });
    }
}