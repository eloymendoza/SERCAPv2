<?php

namespace App\Domain\Puestos\Rules\Update;

use App\Domain\Puestos\Rules\PuestoRuleInterface;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Puestos\DTOs\PuestoDTO;
use App\Exceptions\Domain\BusinessRuleException;

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
                throw BusinessRuleException::withMessage("El sistema bloquea la edición directa del perfil por reglas de SLA. Debe cancelar las vacantes del puesto original en su requisición actual y agregar un nuevo puesto.", "CAMBIO_PERFIL_RESTRINGIDO_SLA");
            }
        }
    }
}