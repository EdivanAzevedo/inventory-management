<?php

namespace App\Domain\User;

use LogicException;

class User
{
    private ?int $id;

    private function __construct(
        ?int         $id,
        private string   $name,
        private string   $email,
        private string   $passwordHash,
        private UserRole $role,
    ) {
        $this->id = $id;
    }

    public static function create(
        string   $name,
        string   $email,
        string   $passwordHash,
        UserRole $role = UserRole::operator,
    ): self {
        return new self(null, $name, $email, $passwordHash, $role);
    }

    public static function reconstitute(
        int      $id,
        string   $name,
        string   $email,
        string   $passwordHash,
        UserRole $role,
    ): self {
        return new self($id, $name, $email, $passwordHash, $role);
    }

    /** Called exclusively by the repository after INSERT to set the auto-increment id. */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new LogicException('User already has an id.');
        }
        $this->id = $id;
    }

    public function changeRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function getId(): ?int       { return $this->id; }
    public function getName(): string    { return $this->name; }
    public function getEmail(): string   { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): UserRole  { return $this->role; }
}
