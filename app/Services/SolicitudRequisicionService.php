<?php

namespace App\Services;

use App\DTOs\SolicitudRequisicionDTO;
use App\Enums\SolicitudRequisicionEstado;
use App\Mappers\SolicitudRequisicionMapper;
use App\Models\SolicitudRequisicion;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Logging\LogContext;

/**
 * Servicio para la gestión de Solicitudes de Requisición.
 *
 * Coordina las transacciones, mapeo de datos y lógica de persistencia.
 */
class SolicitudRequisicionService
{
    use HandlesProcess;

    /**
     * Inicializa una nueva instancia de SolicitudRequisicionService.
     */
    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }



    /**
     * Registra una nueva solicitud de requisición en base de datos.
     *
     * @param SolicitudRequisicionDTO $dto
     * @param string|null $accion Acción a ejecutar (ej. 'emitir')
     * @return SolicitudRequisicionDTO
     */
    public function create(SolicitudRequisicionDTO $dto, ?string $accion = null): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando creación de solicitud: {$dto->folio}");

        return $this->handle(function () use ($dto, $accion) {
            return DB::transaction(function () use ($dto, $accion) {
                $data = $this->mapper->toPersistenceArray($dto);
                
                if ($accion === 'emitir') {
                    $data['estado'] = SolicitudRequisicionEstado::EN_PROCESO;
                } elseif (empty($data['estado'])) {
                    unset($data['estado']);
                }
                
                $model = SolicitudRequisicion::create($data);

                Log::channel($this->logContext->channel())->info("Solicitud creada con ID: {$model->id}");

                return $this->mapper->toDTO($model);
            });
        }, 'SolicitudRequisicionService@create');
    }

    /**
     * Actualiza los datos de una solicitud de requisición existente.
     *
     * @param int $id
     * @param SolicitudRequisicionDTO $dto
     * @param string|null $accion Acción a ejecutar (ej. 'emitir')
     * @return SolicitudRequisicionDTO
     */
    public function update(int $id, SolicitudRequisicionDTO $dto, ?string $accion = null): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Iniciando actualización de solicitud ID: {$id}");

        return $this->handle(function () use ($id, $dto, $accion) {
            return DB::transaction(function () use ($id, $dto, $accion) {
                $model = SolicitudRequisicion::findOrFail($id);
                $data = $this->mapper->toPersistenceArray($dto);
                
                if ($accion === 'emitir') {
                    $data['estado'] = SolicitudRequisicionEstado::EN_PROCESO;
                } elseif ($dto->estado === null) {
                    unset($data['estado']);
                }
                
                $model->update($data);

                Log::channel($this->logContext->channel())->info("Solicitud ID {$id} actualizada con éxito.");

                return $this->mapper->toDTO($model);
            });
        }, 'SolicitudRequisicionService@update');
    }

    /**
     * Busca y retorna una solicitud de requisición específica por su identificador.
     *
     * @param int $id
     * @return SolicitudRequisicionDTO
     */
    public function find(int $id): SolicitudRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Buscando solicitud ID: {$id}");

        return $this->handle(function () use ($id) {
            $model = SolicitudRequisicion::findOrFail($id);
            return $this->mapper->toDTO($model);
        }, 'SolicitudRequisicionService@find');
    }

    /**
     * Retorna una colección de todas las solicitudes registradas.
     *
     * @return array<int, SolicitudRequisicionDTO>
     */
    public function list(): array
    {
        Log::channel($this->logContext->channel())->info("Consultando colección de solicitudes");

        return $this->handle(function () {
            $models = SolicitudRequisicion::all();
            return $this->mapper->toDTOCollection($models);
        }, 'SolicitudRequisicionService@list');
    }

    /**
     * Elimina una solicitud de requisición de la base de datos.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        Log::channel($this->logContext->channel())->info("Eliminando solicitud ID: {$id}");

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = SolicitudRequisicion::findOrFail($id);
                $model->delete();
                Log::channel($this->logContext->channel())->info("Solicitud ID {$id} eliminada.");
            });
        }, 'SolicitudRequisicionService@delete');
    }
}