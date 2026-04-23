<?php

namespace App\Infrastructure\Time;

use App\Domain\Shared\Ports\ClockPort;
use DateTimeImmutable;

class SystemClock implements ClockPort
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
