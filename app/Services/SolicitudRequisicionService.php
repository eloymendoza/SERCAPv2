<?php

namespace App\Services;

use App\DTOs\SolicitudRequisicionDTO;
use App\Mappers\SolicitudRequisicionMapper;
use App\Models\SolicitudRequisicion;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para la gestión de Solicitudes de Requisición.
 *
 * Coordina las transacciones, mapeo de datos y lógica de persistencia.
 */
class SolicitudRequisicionService
{
    use HandlesProcess;

    /**
     * Canal de log configurado para este servicio.
     *
     * @var string
     */
    protected string $logChannel = 'daily';

    /**
     * Inicializa una nueva instancia de SolicitudRequisicionService.
     */
    public function __construct(
        private readonly SolicitudRequisicionMapper $mapper
    ) {}

    /**
     * Registra una nueva solicitud de requisición en base de datos.
     *
     * @param SolicitudRequisicionDTO $dto
     * @return SolicitudRequisicionDTO
     */
    public function create(SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logChannel)->info("Iniciando creación de solicitud: {$dto->folio}");

        return $this->handle(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $data = $this->mapper->toPersistenceArray($dto);
                $model = SolicitudRequisicion::create($data);

                Log::channel($this->logChannel)->info("Solicitud creada con ID: {$model->id}");

                return $this->mapper->toDTO($model);
            });
        }, 'SolicitudRequisicionService@create');
    }

    /**
     * Actualiza los datos de una solicitud de requisición existente.
     *
     * @param int $id
     * @param SolicitudRequisicionDTO $dto
     * @return SolicitudRequisicionDTO
     */
    public function update(int $id, SolicitudRequisicionDTO $dto): SolicitudRequisicionDTO
    {
        Log::channel($this->logChannel)->info("Iniciando actualización de solicitud ID: {$id}");

        return $this->handle(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $model = SolicitudRequisicion::findOrFail($id);
                $data = $this->mapper->toPersistenceArray($dto);
                $model->update($data);

                Log::channel($this->logChannel)->info("Solicitud ID {$id} actualizada con éxito.");

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
        Log::channel($this->logChannel)->info("Buscando solicitud ID: {$id}");

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
        Log::channel($this->logChannel)->info("Consultando colección de solicitudes");

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
        Log::channel($this->logChannel)->info("Eliminando solicitud ID: {$id}");

        $this->handle(function () use ($id) {
            DB::transaction(function () use ($id) {
                $model = SolicitudRequisicion::findOrFail($id);
                $model->delete();
                Log::channel($this->logChannel)->info("Solicitud ID {$id} eliminada.");
            });
        }, 'SolicitudRequisicionService@delete');
    }
}
