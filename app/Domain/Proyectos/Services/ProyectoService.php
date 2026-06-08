<?php

namespace App\Domain\Proyectos\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\Domain\Proyectos\Models\Proyecto;
use App\Domain\Proyectos\Mappers\ProyectoMapper;
use Illuminate\Support\Collection;

class ProyectoService
{
    use HandlesProcess;

    public function __construct(
        private readonly ProyectoMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'proyecto';
    }

    /**
     * Obtiene la colección completa de proyectos activos.
     *
     * @return Collection<int, \App\Domain\Proyectos\DTOs\ProyectoDTO>
     */
    public function getActive(): Collection
    {
        Log::channel($this->logContext->channel())->info("Consultando catálogo de proyectos activos");

        return $this->handle(function () {
            $collection = Proyecto::where('activoProyecto', true)->get();
            
            return $collection->map(function ($model) {
                return $this->mapper->toDTO($model);
            });
        }, 'ProyectoService@getActive');
    }
}