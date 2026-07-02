<?php

namespace App\App\Api\Puestos\Controllers;

use App\App\Api\Controller;
use App\Logging\LogContext;
use Illuminate\Http\JsonResponse;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Domain\Puestos\Services\PuestoService;
use App\App\Api\Puestos\Requests\PuestoRequest;
use App\App\Api\Puestos\Resources\PuestoResource;

class PuestoController extends Controller
{
    public function __construct(
        private readonly PuestoService $puestoService,
        private readonly LogContext $logContext
    ) {
        $this->logContext->setChannel('puestos');
    }

    /**
     * Orquesta el listado de puestos.
     */
    public function index(): JsonResponse
    {
        $paginator = $this->puestoService->paginate(15);

        return response()->json([
            'data' => PuestoResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ]);
    }

    /**
     * Muestra un puesto específico.
     */
    public function show(Puesto $puesto){
        $dto = $this->puestoService->find($puesto);
        
        return response()->json([
            'data' => new PuestoResource($dto)
        ]);
    }

    /**
     * Actualiza un puesto existente.
     */
    public function update(Puesto $puesto, PuestoRequest $request): JsonResponse
    {
        try {
            $dto = PuestoDTO::fromRequest($request->validated());
            $updatedDto = $this->puestoService->update($puesto, $dto);

            return response()->json([
                'data' => new PuestoResource($updatedDto)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Almacena un puesto nuevo.
     */
    public function store(PuestoRequest $request): JsonResponse
    {
        try {
            $dto = PuestoDTO::fromRequest($request->validated());
            $createdDto = $this->puestoService->create($dto);

            return response()->json([
                'message' => 'Puesto creado correctamente.',
                'data' => new PuestoResource($createdDto)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Elimina un puesto lógicamente.
     */
    public function destroy(Puesto $puesto): JsonResponse
    {
        try {
            $this->puestoService->delete($puesto);

            return response()->json([
                'message' => 'Puesto eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}