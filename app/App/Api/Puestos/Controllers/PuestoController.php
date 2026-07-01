<?php

namespace App\App\Api\Puestos\Controllers;

use App\App\Api\Controller;

use App\Logging\LogContext;
use Illuminate\Http\JsonResponse;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\Services\PuestoService;
use App\Domain\Puestos\Actions\VincularPerfilSgcAction;
use App\App\Api\Puestos\Requests\VincularPerfilSgcRequest;

class PuestoController extends Controller
{
    public function __construct(
        private readonly PuestoService $puestoService,
        private readonly VincularPerfilSgcAction $vincularAction,
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
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ]);
    }

    public function show(Puesto $puesto){
        $dto = $this->puestoService->find($puesto);
        
        return response()->json([
            'data' => $dto
        ]);
    }

    /**
     * Vincula un documento SGC oficial a un puesto existente.
     */
    public function vincular(Puesto $puesto, VincularPerfilSgcRequest $request): JsonResponse
    {
        try {
            $perfil = $this->vincularAction->execute($puesto, (int) $request->id_documento);

            return response()->json([
                'message' => 'Perfil vinculado correctamente al SGC',
                'data' => $perfil
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}