<?php

namespace App\Domain\Requisiciones\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;

class SolicitudRequisicionAprobadaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Construye una nueva instancia del evento.
     */
    public function __construct(
        public readonly SolicitudRequisicion $solicitud
    ) {}
}