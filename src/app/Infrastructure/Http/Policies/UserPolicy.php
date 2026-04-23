<?php

namespace App\Infrastructure\Http\Policies;

use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class UserPolicy
{
    public function updateRole(UserModel $user): bool
    {
        return UserRole::from($user->role) === UserRole::admin;
    }
}
