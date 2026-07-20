<?php

namespace App\Domain\Requisiciones\Observers;

use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstadoEnum;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobadaEvent;
use App\Domain\Requisiciones\Actions\AplicarEnmiendaRequisicionAction;

class SolicitudRequisicionObserver
{
    /**
     * Intercepta la actualización del modelo.
     */
    public function updated(SolicitudRequisicion $solicitud): void
    {
        if ($solicitud->wasChanged('estado') && $solicitud->estado === SolicitudRequisicionEstadoEnum::TERMINADO) {
            
            if ($solicitud->esEnmienda()) {
                // Despacha el motor de conciliación para alterar la requisición original
                app(AplicarEnmiendaRequisicionAction::class)->execute($solicitud);
            } else {
                // Flujo estándar: Creación de Requisición primaria
                SolicitudRequisicionAprobadaEvent::dispatch($solicitud);
            }
            
        }
    }
}