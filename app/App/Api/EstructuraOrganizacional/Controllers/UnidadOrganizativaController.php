<?php

namespace App\App\Api\EstructuraOrganizacional\Controllers;

use App\Logging\LogContext;
use Illuminate\Http\JsonResponse;
use App\App\Api\Controller;
use App\App\Api\EstructuraOrganizacional\Requests\UnidadOrganizativaRequest;
use App\App\Api\EstructuraOrganizacional\Resources\UnidadOrganizativaResource;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\Domain\EstructuraOrganizacional\Services\UnidadOrganizativaService;

class UnidadOrganizativaController extends Controller
{
    public function __construct(
        private readonly UnidadOrganizativaService $service,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('estructura_organizacional');
    }

    /**
     * Orquesta el listado integral o paginado del recurso de dominio.
     */
    public function index(): JsonResponse
    {
        $paginator = $this->service->paginate(15);
        
        return response()->json([
            'data' => UnidadOrganizativaResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ]);
    }

    /**
     * Coordina la creación delegando el payload DTO hacia el Service subyacente.
     */
    public function store(UnidadOrganizativaRequest $request): JsonResponse
    {
        $dto = UnidadOrganizativaDTO::fromRequest($request->validated());
        $createdDto = $this->service->create($dto);
        
        return response()->json(new UnidadOrganizativaResource($createdDto), 201);
    }

    /**
     * Retorna el detalle jerárquico instanciando el Service Locator del nodo.
     */
    public function show(int $id): JsonResponse
    {
        $dto = $this->service->find($id);
        
        return response()->json(new UnidadOrganizativaResource($dto));
    }

    /**
     * Canaliza los campos validados para la actualización segmentada del nodo.
     */
    public function update(UnidadOrganizativaRequest $request, int $id): JsonResponse
    {
        $dto = UnidadOrganizativaDTO::fromRequest($request->validated());
        $updatedDto = $this->service->update($id, $dto);
        
        return response()->json(new UnidadOrganizativaResource($updatedDto));
    }

    /**
     * Ejecuta el desprendimiento lógico (destroy) abstrayendo la transacción.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json(null, 204);
    }
}