<?php

namespace App\Application\Auth\RegisterUser;

final class RegisterUserResult
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly string $token,
    ) {}
}
