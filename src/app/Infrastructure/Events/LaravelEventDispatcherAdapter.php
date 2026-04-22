<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\Ports\EventDispatcherPort;
use Illuminate\Contracts\Events\Dispatcher;

class LaravelEventDispatcherAdapter implements EventDispatcherPort
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
