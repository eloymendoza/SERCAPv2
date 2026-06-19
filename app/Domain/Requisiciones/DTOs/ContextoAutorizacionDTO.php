<?php

namespace App\Domain\Requisiciones\DTOs;

use App\Domain\Autenticacion\Models\User;

readonly class ContextoAutorizacionDTO
{
    public function __construct(
        public User $user,
        public ?int $direccionId = null,
        public ?int $proyectoId = null,
    ) {}
}
