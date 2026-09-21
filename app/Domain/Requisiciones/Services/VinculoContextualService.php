<?php

namespace App\Domain\Requisiciones\Services;

use App\Domain\Autenticacion\Models\User;
use App\Domain\Catalogos\Models\Proyecto;
use App\Domain\Requisiciones\DTOs\ContextoAutorizacionDTO;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

class VinculoContextualService
{
    /**
     * Determina si el usuario es el encargado de una dirección organizativa específica.
     */
    public function esDireccionEncargada(User $user, int $unidadOrganizativaId): bool
    {
        $unidad = UnidadOrganizativa::find($unidadOrganizativaId);
        
        if (!$unidad) {
            return false;
        }

        $direccion = $unidad->resolverDireccion();

        return $direccion->encargado_usuario === $user->username;
    }

    /**
     * Determina si el usuario funge como gerente o jefe de un proyecto específico activo.
     */
    public function esVinculadoProyecto(User $user, int $proyectoId): bool
    {
        return Proyecto::where('idProyecto', $proyectoId)
            ->where('activoProyecto', true)
            ->where(fn ($q) => $q
                ->where('gerenteProyecto', $user->name)
                ->orWhere('jefeProyecto', $user->name)
            )->exists();
    }

    /**
     * Determina si el usuario tiene vínculo operativo con la dirección o proyecto dados mediante su contexto.
     */
    public function tieneVinculoContextual(ContextoAutorizacionDTO $contexto): bool
    {
        if ($contexto->unidadOrganizativaId && $this->esDireccionEncargada($contexto->user, $contexto->unidadOrganizativaId)) {
            return true;
        }
        if ($contexto->proyectoId && $this->esVinculadoProyecto($contexto->user, $contexto->proyectoId)) {
            return true;
        }
        return false;
    }
}