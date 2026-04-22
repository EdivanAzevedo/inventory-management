<?php

namespace App\Infrastructure\Transaction;

use App\Domain\Shared\Ports\TransactionPort;
use Illuminate\Support\Facades\DB;

class LaravelTransactionAdapter implements TransactionPort
{
    public function run(callable $fn): mixed
    {
        return DB::transaction($fn);
    }
}
