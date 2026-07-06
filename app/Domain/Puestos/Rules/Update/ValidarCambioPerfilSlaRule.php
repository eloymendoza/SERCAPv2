<?php

namespace App\Domain\Puestos\Rules\Update;

use App\Domain\Puestos\Rules\PuestoRuleInterface;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use Exception;

class ValidarCambioPerfilSlaRule implements PuestoRuleInterface
{
    /**
     * Valida que no se cambie el perfil del puesto si existen procesos de selección activos.
     */
    public function validate(Puesto $puesto, ?PuestoDTO $dto = null): void
    {
        if ($dto && $dto->idDocumento && $puesto->tieneRequisicionesActivas()) {
            $perfilActivo = $puesto->perfilSgc;
            $cambioPerfil = $perfilActivo ? ($perfilActivo->id_documento != $dto->idDocumento) : true;
            
            if ($cambioPerfil) {
                throw new Exception("El sistema bloquea la edición directa del perfil por reglas de SLA. Debe cancelar las vacantes del puesto original en su requisición actual y agregar un nuevo puesto.");
            }
        }
    }
}