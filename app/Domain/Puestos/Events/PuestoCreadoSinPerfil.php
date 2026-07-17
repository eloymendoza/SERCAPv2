<?php

namespace App\Domain\Puestos\Events;

use App\Domain\Puestos\Models\Puesto;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PuestoCreadoSinPerfil
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Puesto $puesto,
        public readonly ?int $elaboradorId = null
    ) {}
}