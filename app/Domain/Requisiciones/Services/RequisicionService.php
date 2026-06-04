<?php

namespace App\Domain\Requisiciones\Services;

use App\Logging\LogContext;
use App\Traits\HandlesProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Requisiciones\DTOs\RequisicionDTO;
use App\Domain\Requisiciones\Mappers\RequisicionMapper;

class RequisicionService
{
    use HandlesProcess;

    public function __construct(
        private readonly RequisicionMapper $mapper,
        private readonly DetalleRequisicionService $detalleService,
        private readonly LogContext $logContext
    ) {}

    protected function getLogChannel(): string
    {
        return 'requisicion';
    }

    /**
     * Procesa la creación de una requisición base y sus detalles.
     */
    public function create(RequisicionDTO $dto, int $solicitudId): RequisicionDTO
    {
        Log::channel($this->logContext->channel())->info("Procesando creación de requisición y detalles.", [
            'solicitud_id' => $solicitudId
        ]);

        return $this->handle(function () use ($dto, $solicitudId) {
            $data = $this->mapper->toPersistenceArray($dto, $solicitudId);
            $model = Requisicion::create($data);
            
            if ($dto->detalle) {
                $this->detalleService->create($dto->detalle, $model->id);
            }

            $model->load('detalles');
            return $this->mapper->toDTO($model);
        }, 'RequisicionService@create');
    }
}
