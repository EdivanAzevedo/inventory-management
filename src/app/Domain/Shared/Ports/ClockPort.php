<?php

namespace App\Domain\Shared\Ports;

use DateTimeImmutable;

interface ClockPort
{
    public function now(): DateTimeImmutable;
}
