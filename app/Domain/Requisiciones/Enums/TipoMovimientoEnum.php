<?php

namespace App\Domain\Requisiciones\Enums;

enum TipoMovimientoEnum: string
{
    case OBRA_DETERMINADA = 'Obra Determinada';
    case TIEMPO_DETERMINADO = 'Tiempo Determinado';
    case TIEMPO_INDETERMINADO = 'Tiempo Indeterminado';
}
