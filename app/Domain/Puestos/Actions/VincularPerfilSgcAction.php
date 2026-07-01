<?php

namespace App\Domain\Puestos\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\Models\PerfilPuesto;
use \App\Domain\Puestos\Events\PerfilPuestoVinculadoSGC;

class VincularPerfilSgcAction
{
    /**
     * Enlaza un documento SGC autorizado a un puesto existente.
     *
     * @param Puesto $puesto
     * @param int $idDocumentoSgc
     * @return PerfilPuesto
     * @throws Exception
     */
    public function execute(Puesto $puesto, int $idDocumentoSgc): PerfilPuesto
    {
        if ($puesto->tienePerfilVinculadoSGC()) {
            throw new Exception('El puesto ya cuenta con un perfil vinculado al SGC.');
        }

        return DB::transaction(function () use ($puesto, $idDocumentoSgc) {
            $perfil = PerfilPuesto::create([
                'puesto_id' => $puesto->id,
                'id_documento' => $idDocumentoSgc,
                'estado' => 'activo',
                'fecha_autorizacion' => now(), 
            ]);

            PerfilPuestoVinculadoSGC::dispatch($perfil);

            return $perfil;
        });
    }
}