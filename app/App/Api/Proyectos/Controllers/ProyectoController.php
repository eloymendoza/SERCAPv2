<?php

namespace App\App\Api\Proyectos\Controllers;

use App\Logging\LogContext;
use App\App\Api\Proyectos\Resources\ProyectoResource;
use App\Domain\Catalogos\Services\ProyectoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\App\Api\Controller;

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
     * Retorna el listado de proyectos activos.
     */
    public function index(): AnonymousResourceCollection
    {
        $collection = $this->service->getActive();
        
        return ProyectoResource::collection($collection);
    }
}