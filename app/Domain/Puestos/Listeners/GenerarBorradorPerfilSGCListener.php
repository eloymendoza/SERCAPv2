<?php

namespace App\Domain\Puestos\Listeners;

use App\Domain\Puestos\Events\PuestoCreadoSinPerfil;
use App\Domain\Puestos\Models\PerfilPuesto;
use App\Domain\Requisiciones\Models\SolicitudPerfilPuesto;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use Illuminate\Support\Facades\DB;

class GenerarBorradorPerfilSGCListener
{
    /**
     * Handle the event.
     */
    public function handle(PuestoCreadoSinPerfil $event): void
    {
        $puesto = $event->puesto;
        
        DB::transaction(function () use ($puesto, $event) {
            $unidad = UnidadOrganizativa::find($puesto->unidad_organizativa_id);
            $encargadoId = $unidad?->resolverDireccion()?->encargado_id;

            // 1. Crear la SolicitudPerfilPuesto automáticamente (en borrador)
            $solicitudPerfil = SolicitudPerfilPuesto::create([
                'solicitante_id' => $encargadoId,
                'elaborador_id' => null, // Queda nulo para la bandeja compartida del ERS
                'unidad_organizativa_id' => $puesto->unidad_organizativa_id,
                'estado' => 'borrador', 
                'observaciones' => 'Solicitud autogenerada desde la creación manual del puesto en catálogo.',
            ]);

            // 2. Ligar el perfil vacío para el nuevo puesto
            PerfilPuesto::create([
                'solicitud_id' => $solicitudPerfil->id,
                'puesto_id' => $puesto->id,
                'estado' => 'borrador',
            ]);
        });
    }
}