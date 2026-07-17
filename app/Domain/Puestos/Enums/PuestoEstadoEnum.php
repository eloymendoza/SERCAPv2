<?php

namespace App\Domain\Puestos\Enums;

enum PuestoEstadoEnum: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case LEGADO = 'legado';
}