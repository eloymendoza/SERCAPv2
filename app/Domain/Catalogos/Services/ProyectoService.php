<?php

namespace App\Domain\Catalogos\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Support\Collection;

class ProyectoService
{
    use HandlesProcess;

    public function __construct(
        private readonly LogContext $logContext
    ) {}

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
        Log::channel($this->logContext->channel())->info("Consultando catálogo de proyectos activos");

        return $this->handle(function () {
            return Proyecto::where('activoProyecto', true)->get();
        }, 'ProyectoService@getActive');
    }
}