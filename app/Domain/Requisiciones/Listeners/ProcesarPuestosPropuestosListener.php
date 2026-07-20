<?php

namespace App\Domain\Requisiciones\Listeners;

use App\Domain\Puestos\Enums\PuestoEstadoEnum;
use Illuminate\Support\Facades\DB;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\Models\PerfilPuesto;
use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobadaEvent;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

class ProcesarPuestosPropuestosListener
{
    /**
     * Intercepta la aprobación de la requisición para convertir las propuestas de puestos en reales.
     */
    public function handle(SolicitudRequisicionAprobadaEvent $event): void
    {
        $solicitud = $event->solicitud;
        $solicitud->loadMissing('requisicion.detalles.propuestaPuesto');

        $requisicion = $solicitud->requisicion;

        if (!$requisicion || $requisicion->detalles->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($solicitud, $requisicion) {
            $direccion = UnidadOrganizativa::find($solicitud->direccion_id);
            $encargadoId = $direccion?->encargado_id ?? $solicitud->solicitante_id;
            
            $solicitudPerfil = null;

            foreach ($requisicion->detalles as $detalle) {
                if ($detalle->propuestaPuesto) {
                    $propuesta = $detalle->propuestaPuesto;

                    // 1. Crear el Puesto maestro
                    $puestoNuevo = Puesto::create([
                        'nombre_puesto' => $propuesta->nombre_puesto,
                        'direccion_id' => $solicitud->direccion_id,
                        'reporta_a_puesto_id' => $propuesta->reporta_a_puesto_id,
                        'tipo' => $propuesta->tipo,
                        'estado' => PuestoEstadoEnum::BORRADOR->value,
                    ]);

                    // 2. Crear la SolicitudPerfilPuesto automáticamente (una sola vez por requisición)
                    if (!$solicitudPerfil) {
                        $solicitudPerfil = SolicitudPerfilPuesto::create([
                            'solicitante_id' => $encargadoId,
                            'elaborador_id' => null, // Queda nulo para la bandeja compartida del ERS
                            'direccion_id' => $solicitud->direccion_id,
                            'estado' => 'borrador', 
                            'observaciones' => 'Solicitud autogenerada desde la requisición de personal ' . $solicitud->folio,
                        ]);
                    }

                    // 3. Ligar el perfil vacío para el nuevo puesto
                    PerfilPuesto::create([
                        'solicitud_id' => $solicitudPerfil->id,
                        'puesto_id' => $puestoNuevo->id,
                        'estado' => 'borrador',
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