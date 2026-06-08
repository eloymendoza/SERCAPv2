<?php

namespace App\Domain\Proyectos\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\Log;
use App\Domain\Proyectos\Models\Proyecto;
use App\Domain\Proyectos\Mappers\ProyectoMapper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * Obtiene el listado de proyectos paginado.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        Log::channel($this->logContext->channel())->info("Consultando colección paginada de proyectos");

        return $this->handle(function () use ($perPage) {
            $paginator = Proyecto::paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'ProyectoService@paginate');
    }
}