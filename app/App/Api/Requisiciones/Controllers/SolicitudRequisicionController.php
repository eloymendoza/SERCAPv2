<?php

namespace App\App\Api\Requisiciones\Controllers;

use App\Logging\LogContext;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\App\Api\Controller;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\App\Api\Requisiciones\Requests\AprobarSolicitudRequest;
use App\Domain\Requisiciones\Services\SolicitudRequisicionService;
use App\App\Api\Requisiciones\Requests\SolicitudRequisicionRequest;
use App\App\Api\Requisiciones\Resources\SolicitudRequisicionResource;

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

    public function store(SolicitudRequisicionRequest $request): JsonResponse
    {
        $dtoOutput = $this->service->create($request->toDTO(), $request->user());

        return response()->json([
            'data' => new SolicitudRequisicionResource($dtoOutput),
        ], Response::HTTP_CREATED);
    }

    public function show(SolicitudRequisicion $solicitud): SolicitudRequisicionResource
    {
        $dto = $this->service->find($solicitud);

        return new SolicitudRequisicionResource($dto);
    }

    public function update(SolicitudRequisicionRequest $request, SolicitudRequisicion $solicitud): SolicitudRequisicionResource 
    {
        $dtoOutput = $this->service->update($solicitud, $request->toDTO($solicitud->id), $request->user());
        
        return new SolicitudRequisicionResource($dtoOutput);
    }

    /**
     * Procesa la eliminación física de una solicitud de requisición.
     */
    public function destroy(SolicitudRequisicion $solicitud): JsonResponse
    {
        $this->service->delete($solicitud);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Retorna los firmantes que se asignarían si la solicitud se emitiera.
     */
    public function previewAprobadores(SolicitudRequisicion $solicitud): JsonResponse
    {
        $resultado = $this->service->previewAprobadores($solicitud);

        return response()->json(['data' => $resultado]);
    }

    /**
     * Procesa la aprobación del paso activo del workflow para la solicitud dada.
     */
    public function aprobar(AprobarSolicitudRequest $request, SolicitudRequisicion $solicitud): JsonResponse
    {
        $dto = $this->service->aprobar(
            $request->user(),
            $solicitud,
            $request->input('observaciones')
        );

        return response()->json(['data' => new SolicitudRequisicionResource($dto)]);
    }
}