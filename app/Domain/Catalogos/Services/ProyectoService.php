<?php

namespace App\Domain\Catalogos\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Collection;
use App\Domain\Catalogos\Models\Proyecto;

class ProyectoService
{
    use HandlesProcess;

    public function __construct() {}

    protected function getLogChannel(): string
    {
        return 'proyecto';
    }

    /**
     * Obtiene la colección completa de proyectos activos.
     *
     * @return Collection<int, Proyecto>
     */
    public function getActive(): Collection
    {
        $this->logger()->info("Consultando catálogo de proyectos activos");

        return $this->handle(function () {
            return Proyecto::where('activoProyecto', true)->get();
        }, 'ProyectoService@getActive');
    }
}