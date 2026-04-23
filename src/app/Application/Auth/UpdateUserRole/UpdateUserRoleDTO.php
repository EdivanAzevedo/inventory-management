<?php

namespace App\Application\Auth\UpdateUserRole;

final class UpdateUserRoleDTO
{
    public function __construct(
        public readonly int    $userId,
        public readonly string $role,
    ) {}
}
