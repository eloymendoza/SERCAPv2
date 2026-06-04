<?php

namespace App\Domain\Requisiciones\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domain\Requisiciones\Models\DetalleRequisicion;
use App\Domain\Requisiciones\DTOs\DetalleRequisicionDTO;
use App\Domain\Requisiciones\Mappers\DetalleRequisicionMapper;

class DetalleRequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly DetalleRequisicionMapper $mapper,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    /**
     * Registra un nuevo detalle vinculado a una requisición.
     */
    public function create(DetalleRequisicionDTO $dto, int $requisicionId): DetalleRequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Registrando detalle de requisición.", [
            'requisicion_id' => $requisicionId,
            'puesto_id' => $dto->puestoId
        ]);

        return $this->handle(function () use ($dto, $requisicionId) {
            $data = $this->mapper->toPersistenceArray($dto, $requisicionId);
            $model = DetalleRequisicion::create($data);
            return $this->mapper->toDTO($model);
        }, 'DetalleRequisicionService@create');
    }
}
