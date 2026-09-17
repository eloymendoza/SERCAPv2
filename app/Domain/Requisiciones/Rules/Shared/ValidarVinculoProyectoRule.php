<?php

namespace App\Domain\Requisiciones\Rules\Shared;

use App\Domain\Autenticacion\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\Requisiciones\Services\VinculoContextualService;
use App\Domain\Requisiciones\Rules\SolicitudRequisicionRuleInterface;

class ValidarVinculoProyectoRule implements SolicitudRequisicionRuleInterface
{
    public function __construct(
        private readonly ?VinculoContextualService $vinculoService = null
    ) {}

    /**
     * Valida que el solicitante tenga vínculo operativo con la dirección o proyecto de la solicitud.
     */
    public function validate(?SolicitudRequisicion $model, ?SolicitudRequisicionDTO $dto = null): void
    {
        $solicitanteId = $dto ? $dto->solicitanteId : ($model ? $model->solicitante_id : null);
        $direccionId = $dto ? $dto->direccionId : ($model ? $model->direccion_id : null);
        $proyectoId = $dto ? $dto->proyectoId : ($model ? $model->proyecto_id : null);

        if (!$solicitanteId) {
            return;
        }

        $usuario = User::where('id_personal', $solicitanteId)->first();

        if (!$usuario) {
            throw BusinessRuleException::withMessage(
                'El solicitante especificado no es un usuario válido del sistema.',
                'SOLICITANTE_INVALIDO'
            );
        }

        $service = $this->vinculoService ?? app(VinculoContextualService::class);
        $contexto = new ContextoAutorizacionDTO($usuario, $direccionId, $proyectoId);
        
        if (!$service->tieneVinculoContextual($contexto)) {
            throw BusinessRuleException::withMessage(
                'El solicitante debe estar directamente ligado a la dirección (como Director) o al proyecto (como Gerente/Jefe de Proyecto). No se admiten solicitantes ajenos.',
                'VINCULO_PROYECTO_INVALIDO'
            );
        }
    }
}