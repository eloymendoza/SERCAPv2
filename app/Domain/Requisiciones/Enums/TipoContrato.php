<?php

namespace App\Domain\Requisiciones\Enums;

enum TipoContrato: string
{
    case OBRA_DETERMINADA = 'obra_determinada';
    case TIEMPO_DETERMINADO = 'tiempo_determinado';
    case TIEMPO_INDETERMINADO = 'tiempo_indeterminado';
}
