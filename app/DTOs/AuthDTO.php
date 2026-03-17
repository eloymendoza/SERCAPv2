<?php

namespace App\DTOs;

class AuthDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}

    public function toArray(): array {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            username: $data['username'],
            password: $data['password'],
        );
    }
}