<?php

namespace App\Domain\Requisiciones\Observers;

use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\Domain\Requisiciones\Events\SolicitudRequisicionAprobada;

class SolicitudRequisicionObserver
{
    /**
     * Intercepta la actualización del modelo.
     */
    public function updated(SolicitudRequisicion $solicitud): void
    {
        if ($solicitud->wasChanged('estado') && $solicitud->estado === SolicitudRequisicionEstado::TERMINADO) {
            SolicitudRequisicionAprobada::dispatch($solicitud);
        }
    }
}