<?php

namespace App\App\Api\Controllers;

use App\Logging\LogContext;
use App\App\Api\Resources\ProyectoResource;
use App\Domain\Proyectos\Services\ProyectoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controlador de API para el catálogo de Proyectos.
 */
class ProyectoController extends Controller
{
    public function __construct(
        private readonly ProyectoService $service,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('proyecto');
    }

    /**
     * Retorna el listado de proyectos.
     */
    public function index(): AnonymousResourceCollection
    {
        $paginator = $this->service->paginate();
        
        return ProyectoResource::collection($paginator);
    }
}