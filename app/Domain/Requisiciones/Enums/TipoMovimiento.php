<?php

namespace App\Domain\Requisiciones\Enums;

enum TipoMovimiento: string
{
    case NUEVO_INGRESO = 'nuevo_ingreso';
    case REINGRESO = 'reingreso';
    case MOVIMIENTO_INTERNO = 'movimiento_interno';
}
