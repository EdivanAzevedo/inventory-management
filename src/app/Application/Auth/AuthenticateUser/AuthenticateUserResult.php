<?php

namespace App\Application\Auth\AuthenticateUser;

final class AuthenticateUserResult
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly string $token,
    ) {}
}
