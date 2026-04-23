<?php

namespace App\Domain\User\Ports;

use App\Domain\User\User;

interface UserRepositoryPort
{
    public function save(User $user): void;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function existsByEmail(string $email): bool;
}
