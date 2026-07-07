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
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('viewAny', Puesto::class);

        $perPage = request()->input('per_page', 15);
        $paginator = $this->puestoService->paginate((int)$perPage);

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
        Gate::authorize('view', $puesto);

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
        Gate::authorize('delete', $puesto);

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