<?php

namespace App\Domain\Requisiciones\Enums;

enum TipoProyecto: string
{
    case CORPORATIVO = "0";
    case CLV = "1";

    public function label(): string
    {
        return match($this) {
            self::CORPORATIVO => 'Corporativo',
            self::CLV => 'CLV',
        };
    }
}