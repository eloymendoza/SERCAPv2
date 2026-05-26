<?php

namespace App\Http\Controllers;

use App\DTOs\SolicitudRequisicionDTO;
use App\Http\Requests\SolicitudRequisicionRequest;
use App\Mappers\SolicitudRequisicionMapper;
use App\Services\SolicitudRequisicionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Logging\LogContext;

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
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('requisicion');
    }

    /**
     * Retorna una colección de todas las solicitudes registradas.
     */
    public function index(): JsonResponse
    {
        $dtos = $this->service->list();
        $response = array_map(fn($dto) => $this->mapper->toResponseArray($dto), $dtos);

        return response()->json([
            'data' => $response,
        ]);
    }

    /**
     * Procesa la creación e inserción de una nueva solicitud de requisición.
     */
    public function store(SolicitudRequisicionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $accion = $validated['accion'] ?? null;
        
        $dtoInput = SolicitudRequisicionDTO::fromArray($validated);
        $dtoOutput = $this->service->create($dtoInput, $accion);
        $response = $this->mapper->toResponseArray($dtoOutput);

        return response()->json([
            'data' => $response,
        ], Response::HTTP_CREATED);
    }

    /**
     * Retorna el detalle completo de una solicitud de requisición específica.
     */
    public function show(int $id): JsonResponse
    {
        $dto = $this->service->find($id);
        $response = $this->mapper->toResponseArray($dto);

        return response()->json([
            'data' => $response,
        ]);
    }

    /**
     * Procesa la actualización de los datos de una solicitud existente.
     */
    public function update(SolicitudRequisicionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $accion = $validated['accion'] ?? null;
        
        $data = array_merge($validated, ['id' => $id]);
        $dtoInput = SolicitudRequisicionDTO::fromArray($data);
        $dtoOutput = $this->service->update($id, $dtoInput, $accion);
        $response = $this->mapper->toResponseArray($dtoOutput);

        return response()->json([
            'data' => $response,
        ]);
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
