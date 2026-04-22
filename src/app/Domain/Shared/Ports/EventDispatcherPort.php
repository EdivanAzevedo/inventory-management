<?php

namespace App\Domain\Shared\Ports;

interface EventDispatcherPort
{
    public function dispatch(object $event): void;
}
