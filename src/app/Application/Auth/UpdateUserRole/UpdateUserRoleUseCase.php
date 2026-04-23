<?php

namespace App\Application\Auth\UpdateUserRole;

use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\User;
use App\Domain\User\UserRole;

class UpdateUserRoleUseCase
{
    public function __construct(
        private UserRepositoryPort $repository,
    ) {}

    public function execute(UpdateUserRoleDTO $dto): User
    {
        $user = $this->repository->findById($dto->userId);

        if ($user === null) {
            throw new UserNotFoundException($dto->userId);
        }

        $user->changeRole(UserRole::from($dto->role));

        $this->repository->save($user);

        return $user;
    }
}
