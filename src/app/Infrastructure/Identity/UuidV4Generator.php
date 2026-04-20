<?php

namespace App\Infrastructure\Identity;

use App\Domain\Shared\Ports\IdGeneratorPort;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidV4Generator implements IdGeneratorPort
{
    public function generate(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
