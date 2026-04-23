<?php

namespace App\Infrastructure\Http\Policies;

use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class ProductPolicy
{
    public function viewAny(UserModel $user): bool
    {
        return true;
    }

    public function view(UserModel $user): bool
    {
        return true;
    }

    public function create(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function update(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function delete(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin);
    }

    public function reactivate(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin);
    }

    public function addVariant(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function removeVariant(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin);
    }

    private function hasRole(UserModel $user, UserRole ...$allowed): bool
    {
        return in_array(UserRole::from($user->role), $allowed, strict: true);
    }
}
