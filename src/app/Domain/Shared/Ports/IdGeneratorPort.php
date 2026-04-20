<?php

namespace App\Domain\Shared\Ports;

use Ramsey\Uuid\UuidInterface;

interface IdGeneratorPort
{
    public function generate(): UuidInterface;
}
