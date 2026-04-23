<?php

namespace Database\Seeders;

use App\Domain\Shared\Ports\PasswordHasherPort;
use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function __construct(
        private UserRepositoryPort $repository,
        private PasswordHasherPort $hasher,
    ) {}

    public function run(): void
    {
        $this->createIfNotExists('Administrador', 'admin@example.com',    UserRole::admin);
        $this->createIfNotExists('Operador',      'operator@example.com', UserRole::operator);
        $this->createIfNotExists('Visualizador',  'viewer@example.com',   UserRole::viewer);
    }

    private function createIfNotExists(string $name, string $email, UserRole $role): void
    {
        if ($this->repository->existsByEmail($email)) {
            return;
        }

        $user = User::create(
            name:         $name,
            email:        $email,
            passwordHash: $this->hasher->hash('password'),
            role:         $role,
        );

        $this->repository->save($user);
    }
}
