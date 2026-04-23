<?php

namespace App\Application\Auth\AuthenticateUser;

use App\Domain\Shared\Ports\PasswordHasherPort;
use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\Ports\UserTokenPort;

class AuthenticateUserUseCase
{
    public function __construct(
        private UserRepositoryPort $repository,
        private UserTokenPort      $tokenPort,
        private PasswordHasherPort $hasher,
    ) {}

    public function execute(AuthenticateUserDTO $dto): AuthenticateUserResult
    {
        $user = $this->repository->findByEmail($dto->email);

        if ($user === null || !$this->hasher->verify($dto->password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException();
        }

        $token = $this->tokenPort->createToken($user->getId(), 'auth-token');

        return new AuthenticateUserResult(
            name:  $user->getName(),
            email: $user->getEmail(),
            role:  $user->getRole()->value,
            token: $token,
        );
    }
}
