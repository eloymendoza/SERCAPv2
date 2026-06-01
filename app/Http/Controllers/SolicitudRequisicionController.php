<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\DTOs\SolicitudRequisicionDTO;
use App\Enums\SolicitudRequisicionEstado;
use App\Mappers\SolicitudRequisicionMapper;
use App\Services\SolicitudRequisicionService;
use App\Http\Requests\SolicitudRequisicionRequest;

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
        private readonly SolicitudRequisicionMapper $mapper
    ) {
    }

    /**
     * Retorna una colección de todas las solicitudes registradas.
     */
    public function index(): JsonResponse
    {
        $paginator = $this->service->paginate();
        $response = collect($paginator->items())->map(fn($dto) => $this->mapper->toResponseArray($dto));

        return response()->json([
            'data' => $response,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
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
        
        if ($accion === 'emitir') {
            $dtoInput = $dtoInput->withEstado(SolicitudRequisicionEstado::EN_PROCESO);
        }

        $dtoOutput = $this->service->create($dtoInput);
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
        
        if ($accion === 'emitir') {
            $dtoInput = $dtoInput->withEstado(SolicitudRequisicionEstado::EN_PROCESO);
        }

        $dtoOutput = $this->service->update($id, $dtoInput);
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
