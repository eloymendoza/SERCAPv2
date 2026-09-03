<?php

namespace App\App\Api\EstructuraOrganizacional\Controllers;

use App\Logging\LogContext;
use App\App\Api\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;
use App\Domain\EstructuraOrganizacional\DTOs\UnidadOrganizativaDTO;
use App\App\Api\EstructuraOrganizacional\Requests\UnidadOrganizativaRequest;
use App\App\Api\EstructuraOrganizacional\Resources\UnidadOrganizativaResource;
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
     * Obtiene la lista de unidades organizativas.
     */
    public function index(Request $request): JsonResponse
    {
        $nivel = $request->query('nivel');
        $paginator = $this->service->paginate(15, $nivel);
        
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
     * Crea una nueva unidad organizativa.
     */
    public function store(UnidadOrganizativaRequest $request): JsonResponse
    {
        $dto = UnidadOrganizativaDTO::fromRequest($request->validated());
        $createdDto = $this->service->create($dto);
        
        return response()->json(new UnidadOrganizativaResource($createdDto), 201);
    }

    /**
     * Obtiene los detalles de una unidad organizativa específica.
     */
    public function show(UnidadOrganizativa $unidad): JsonResponse
    {
        $dto = $this->service->find($unidad);
        
        return response()->json(new UnidadOrganizativaResource($dto));
    }

    /**
     * Actualiza los datos de una unidad organizativa.
     */
    public function update(UnidadOrganizativaRequest $request, UnidadOrganizativa $unidad): JsonResponse
    {
        $dto = UnidadOrganizativaDTO::fromRequest($request->validated());
        $updatedDto = $this->service->update($unidad, $dto);
        
        return response()->json(new UnidadOrganizativaResource($updatedDto));
    }

    /**
     * Elimina una unidad organizativa.
     */
    public function destroy(UnidadOrganizativa $unidad): JsonResponse
    {
        $this->service->delete($unidad);
        
        return response()->json(null, 204);
    }

    /**
     * Activa una unidad organizativa e inactiva a la que reemplaza.
     */
    public function activate(UnidadOrganizativa $unidad): JsonResponse
    {
        $this->service->activate($unidad);
        
        return response()->json([
            'message' => 'Unidad organizativa activada exitosamente.'
        ], 200);
    }

    /**
     * Desactiva manualmente una unidad organizativa.
     */
    public function deactivate(UnidadOrganizativa $unidad): JsonResponse
    {
        $this->service->deactivate($unidad);
        
        return response()->json([
            'message' => 'Unidad organizativa desactivada exitosamente.'
        ], 200);
    }
}