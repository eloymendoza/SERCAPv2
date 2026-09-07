<?php

namespace App\Domain\EstructuraOrganizacional\Enums;

enum NivelUnidadOrganizativoEnum: string
{
    case PRESIDENCIA = 'presidencia';
    case DIRECCION = 'direccion';
    case GERENCIA = 'gerencia';
    case AREA = 'area';
}