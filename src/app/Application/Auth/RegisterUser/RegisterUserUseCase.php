<?php

namespace App\Application\Auth\RegisterUser;

use App\Domain\Shared\Ports\PasswordHasherPort;
use App\Domain\User\Exceptions\UserAlreadyExistsException;
use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\Ports\UserTokenPort;
use App\Domain\User\User;
use App\Domain\User\UserRole;

class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserTokenPort      $tokenPort,
        private PasswordHasherPort $hasher,
    ) {}

    public function execute(RegisterUserDTO $dto): RegisterUserResult
    {
        if ($this->repository->existsByEmail($dto->email)) {
            throw new UserAlreadyExistsException($dto->email);
        }

        $user = User::create(
            name:         $dto->name,
            email:        $dto->email,
            passwordHash: $this->hasher->hash($dto->password),
            role:         UserRole::operator,
        );

        $this->repository->save($user);

        $token = $this->tokenPort->createToken($user->getId(), 'auth-token');

        return new RegisterUserResult(
            name:  $user->getName(),
            email: $user->getEmail(),
            role:  $user->getRole()->value,
            token: $token,
        );
    }
}
