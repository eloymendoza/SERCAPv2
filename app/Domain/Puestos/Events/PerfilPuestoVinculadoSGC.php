<?php

namespace App\Domain\Puestos\Events;

use Illuminate\Queue\SerializesModels;
use App\Domain\Puestos\Models\PerfilPuesto;
use Illuminate\Foundation\Events\Dispatchable;

class PerfilPuestoVinculadoSGC
{
    use Dispatchable, SerializesModels;

    public PerfilPuesto $perfilPuesto;

    /**
     * Crea una nueva instancia del evento.
     */
    public function __construct(PerfilPuesto $perfilPuesto)
    {
        $this->perfilPuesto = $perfilPuesto;
    }
}