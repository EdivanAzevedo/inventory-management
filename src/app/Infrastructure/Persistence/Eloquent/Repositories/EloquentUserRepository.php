<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepositoryPort
{
    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $model = UserModel::create([
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'password' => $user->getPasswordHash(),
                'role'     => $user->getRole()->value,
            ]);

            $user->assignId($model->id);
            return;
        }

        UserModel::where('id', $user->getId())->update([
            'name'  => $user->getName(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole()->value,
        ]);
    }

    public function findById(int $id): ?User
    {
        $model = UserModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }

    private function toDomain(UserModel $model): User
    {
        return User::reconstitute(
            id:           $model->id,
            name:         $model->name,
            email:        $model->email,
            passwordHash: $model->getAttributes()['password'],
            role:         UserRole::from($model->role),
        );
    }
}
