<?php

namespace App\Domain\EstructuraOrganizacional\Enums;

enum UnidadOrganizativaEstadoEnum: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case LEGADO = 'legado';
}