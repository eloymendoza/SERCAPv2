<?php

namespace App\DTOs;

class AuthDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}
}