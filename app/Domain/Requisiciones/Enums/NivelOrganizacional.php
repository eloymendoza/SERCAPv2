<?php

namespace App\Domain\Requisiciones\Enums;

enum NivelOrganizacional: string
{
    case OPERATIVO = 'operativo';
    case MANDO_MEDIO = 'mando_medio';
    case ALTA_DIRECCION = 'alta_direccion';
}
