<?php

namespace App\App\Api\Requisiciones\Rules;

use Closure;
use App\Domain\Autenticacion\Models\User;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

class ValidarVinculoProyecto implements ValidationRule
{
    public function __construct(
        protected ?int $direccionId,
        protected ?int $proyectoId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $usuario = User::where('id_personal', $value)->first();
        
        if (!$usuario) {
            $fail('El solicitante especificado no es un usuario válido del sistema.');
            return;
        }

        if ($this->direccionId) {
            $direccion = UnidadOrganizativa::find($this->direccionId);
            if ($direccion && (int)$direccion->encargado_id === (int)$usuario->id_personal) {
                return;
            }
        }

        if ($this->proyectoId) {
            $proyecto = Proyecto::find($this->proyectoId);
            if ($proyecto) {
                if (trim($proyecto->gerenteProyecto) === trim($usuario->name) ||
                    trim($proyecto->jefeProyecto) === trim($usuario->name)) {
                    return;
                }
            }
        }

        $fail('El solicitante debe estar directamente ligado a la dirección (como Director) o al proyecto (como Gerente/Jefe de Proyecto). No se admiten solicitantes ajenos.');
    }
}
