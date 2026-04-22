<?php

namespace App\Domain\Shared\Ports;

interface TransactionPort
{
    /** @template T @param callable(): T $fn @return T */
    public function run(callable $fn): mixed;
}
