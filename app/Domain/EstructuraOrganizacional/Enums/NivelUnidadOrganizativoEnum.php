<?php

namespace App\Domain\EstructuraOrganizacional\Enums;

enum NivelUnidadOrganizativoEnum: string
{
    case DIRECCION = 'direccion';
    case GERENCIA = 'gerencia';
    case AREA = 'area';
}