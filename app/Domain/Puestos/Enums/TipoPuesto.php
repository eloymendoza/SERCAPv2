<?php

namespace App\Domain\Puestos\Enums;

enum TipoPuesto: string
{
    case DIRECTO = 'directo';
    case INDIRECTO = 'indirecto';
}