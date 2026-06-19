<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use App\Domain\Autenticacion\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\Requisiciones\Services\VinculoContextualService;

class ValidarVinculoProyectoRule implements ValidationRule
{
    public function __construct(
        protected ?int $direccionId,
        protected ?int $proyectoId
    ) {}

    /**
     * Valida que el solicitante tenga vínculo operativo con la dirección o proyecto de la solicitud.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $usuario = User::where('id_personal', $value)->first();

        if (!$usuario) {
            $fail('El solicitante especificado no es un usuario válido del sistema.');
            return;
        }

        $vinculoService = app(VinculoContextualService::class);
        $contexto = new ContextoAutorizacionDTO($usuario, $this->direccionId, $this->proyectoId);
        
        if (!$vinculoService->tieneVinculoContextual($contexto)) {
            $fail('El solicitante debe estar directamente ligado a la dirección (como Director) o al proyecto (como Gerente/Jefe de Proyecto). No se admiten solicitantes ajenos.');
        }
    }
}