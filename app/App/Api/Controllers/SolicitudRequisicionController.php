<?php

namespace App\App\Api\Controllers;

use App\Logging\LogContext;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\App\Api\Requests\SolicitudRequisicionRequest;
use App\App\Api\Resources\SolicitudRequisicionResource;
use App\Domain\Requisiciones\Services\SolicitudRequisicionService;

/**
 * Controlador de API para gestionar el recurso de Solicitudes de Requisición.
 */
class SolicitudRequisicionController extends Controller
{
    /**
     * Inicializa una nueva instancia de SolicitudRequisicionController.
     */
    public function __construct(
        private readonly SolicitudRequisicionService $service,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('requisicion');
    }

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $paginator = $this->service->paginate();
        
        return SolicitudRequisicionResource::collection($paginator);
    }

    /**
     * Procesa la creación e inserción de una nueva solicitud de requisición.
     */
    public function store(SolicitudRequisicionRequest $request): JsonResponse
    {
        $dtoInput = $request->toDTO();
        $dtoOutput = $this->service->create($dtoInput);

        return response()->json([
            'data' => new SolicitudRequisicionResource($dtoOutput),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): SolicitudRequisicionResource
    {
        $dto = $this->service->find($id);

        return new SolicitudRequisicionResource($dto);
    }

    /**
     * Procesa la actualización de los datos de una solicitud existente.
     */
    public function update(SolicitudRequisicionRequest $request, int $id): SolicitudRequisicionResource
    {
        $dtoInput = $request->toDTO($id);
        $dtoOutput = $this->service->update($id, $dtoInput);

        return new SolicitudRequisicionResource($dtoOutput);
    }

    /**
     * Procesa la eliminación física de una solicitud de requisición.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
