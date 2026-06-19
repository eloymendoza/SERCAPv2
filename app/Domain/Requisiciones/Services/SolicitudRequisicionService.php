<?php

namespace App\Domain\Requisiciones\Services;

use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use App\Domain\Autenticacion\Models\User;
use App\Domain\Workflows\Services\WorkflowService;
use App\Domain\Workflows\Services\OrquestadorWorkflow;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\Domain\Requisiciones\Mappers\SolicitudRequisicionMapper;

class SolicitudRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly RequisicionService $requisicionService,
        private readonly OrquestadorWorkflow $orquestadorWorkflow,
        private readonly FirmantesResolverService $firmantesResolver,
        private readonly WorkflowService $workflowService,
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    public function create(SolicitudRequisicionDTO $dto, User $elaborador): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando creación de solicitud.", [
            'folio' => $dto->folio
        ]);

        return $this->handle(function () use ($dto, $elaborador) {
            $createdModel = DB::transaction(function () use ($dto, $elaborador) {
                $data = $this->mapper->toPersistenceArray($dto);
                $model = SolicitudRequisicion::create($data);
                
                if ($dto->requisicion) {
                    $this->requisicionService->create($dto->requisicion, $model->id);
                }

                $model->load('requisicion.detalles');

                if ($dto->accion === 'emitir') {
                    $resultadoFirmantes = $this->firmantesResolver->resolverParaRequisicion($elaborador, $model);
                    $this->orquestadorWorkflow->emitir($model, $elaborador, $resultadoFirmantes);
                }

                return $model;
            });

            $this->logger()->info("Solicitud creada.", [
                'id' => $createdModel->id
            ]);

            return $this->mapper->toDTO($createdModel);
        }, 'SolicitudRequisicionService@create');
    }

    public function update(SolicitudRequisicion $model, SolicitudRequisicionDTO $dto, User $elaborador): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando actualización de solicitud.", [
            'id' => $model->id
        ]);

        return $this->handle(function () use ($model, $dto, $elaborador) {
            $updatedModel = DB::transaction(function () use ($model, $dto, $elaborador) {
                $data = $this->mapper->toUpdatePersistenceArray($dto);
                $model->update($data);

                if ($dto->requisicion) {
                    $this->requisicionService->update($dto->id, $model->id);
                }

                $model->refresh()->load('requisicion.detalles');

                if ($dto->accion === 'emitir') {
                    $resultadoFirmantes = $this->firmantesResolver->resolverParaRequisicion($elaborador, $model);
                    $this->orquestadorWorkflow->emitir($model, $elaborador, $resultadoFirmantes);
                }

                return $model;
            });

            $this->logger()->info("Solicitud actualizada.", [
                'id' => $updatedModel->id
            ]);

            return $this->mapper->toDTO($updatedModel);
        }, 'SolicitudRequisicionService@update');
    }

    public function find(SolicitudRequisicion $model): SolicitudRequisicionDTO
    {
        $this->logger()->info("Consultando detalle de solicitud.", [
            'id' => $model->id
        ]);

        return $this->handle(function () use ($model) {
            return $this->mapper->toDTO($model);
        }, 'SolicitudRequisicionService@find');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $this->logger()->info("Consultando colección paginada de solicitudes");

        return $this->handle(function () use ($perPage) {
            $paginator = SolicitudRequisicion::paginate($perPage);
            
            $paginator->getCollection()->transform(function ($model) {
                return $this->mapper->toDTO($model);
            });

            return $paginator;
        }, 'SolicitudRequisicionService@paginate');
    }

    public function delete(SolicitudRequisicion $model): void
    {
        $this->logger()->info("Iniciando eliminación de solicitud.", [
            'id' => $model->id
        ]);

        $this->handle(function () use ($model) {
            DB::transaction(function () use ($model) {
                if ($model->requisicion) {
                    $this->requisicionService->delete($model->requisicion->id);
                }
                
                $model->delete();
            });

            $this->logger()->info("Solicitud eliminada.", [
                'id' => $model->id
            ]);
        }, 'SolicitudRequisicionService@delete');
    }

    /**
     * Emite una solicitud: resuelve firmantes, invoca workflow si aplica, y transiciona el estado.
     */
    public function emitir(User $elaborador, SolicitudRequisicion $solicitud): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando emisión de solicitud.", [
            'id' => $solicitud->id,
            'elaborador' => $elaborador->id_personal
        ]);

        return $this->handle(function () use ($elaborador, $solicitud) {
            $solicitud->load('requisicion.detalles');
            $resultadoFirmantes = $this->firmantesResolver->resolverParaRequisicion($elaborador, $solicitud);

            return DB::transaction(function () use ($solicitud, $elaborador, $resultadoFirmantes) {
                $this->orquestadorWorkflow->emitir($solicitud, $elaborador, $resultadoFirmantes);
                
                $this->logger()->info("Proceso de emisión completado.", [
                    'id' => $solicitud->id,
                    'requiere_workflow' => $resultadoFirmantes['requiere_workflow'],
                    'estado_final' => $solicitud->estado->value
                ]);

                return $this->mapper->toDTO($solicitud);
            });
        }, 'SolicitudRequisicionService@emitir');
    }

    /**
     * Procesa la aprobación de un paso del workflow para la solicitud dada.
     *
     * Delega la operación al WorkflowService y sincroniza el estado local
     * con el estado global de la instancia devuelto por Django.
     */
    public function aprobar(User $firmante, SolicitudRequisicion $solicitud, ?string $observaciones = null): SolicitudRequisicionDTO
    {
        $this->logger()->info("Iniciando aprobación de solicitud.", [
            'id'            => $solicitud->id,
            'id_instancia'  => $solicitud->id_instancia_workflow,
            'firmante'      => $firmante->id_personal,
        ]);

        return $this->handle(function () use ($firmante, $solicitud, $observaciones) {
            $workflowResponse = $this->orquestadorWorkflow->aprobarPaso($solicitud, $firmante, $observaciones);

            $this->logger()->info("Aprobación procesada.", [
                'id'            => $solicitud->id,
                'estado_django' => $workflowResponse->estado,
                'estado_local'  => $solicitud->estado->value,
            ]);

            return $this->mapper->toDTO($solicitud->fresh());
        }, 'SolicitudRequisicionService@aprobar');
    }

    /**
     * Calcula los firmantes que tendría la solicitud sin persistir nada.
     *
     * @return array{requiere_workflow: bool, workflow_id: int, firmantes: array}
     */
    public function previewAprobadores(SolicitudRequisicion $solicitud): array
    {
        $this->logger()->info("Consultando preview de aprobadores.", ['id' => $solicitud->id]);

        return $this->handle(function () use ($solicitud) {
            $solicitud->load('requisicion.detalles');
            
            $elaborador = User::where('id_personal', $solicitud->elaborador_id)->firstOrFail();
            
            return $this->firmantesResolver->resolverParaRequisicion($elaborador, $solicitud);
        }, 'SolicitudRequisicionService@previewAprobadores');
    }
}