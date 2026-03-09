<?php

namespace App\DTOs;

use App\Http\Requests\LoginRequest;

class LoginDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            username: $request->validated('username'),
            password: trim($request->validated('password')),
        );
    }
}
