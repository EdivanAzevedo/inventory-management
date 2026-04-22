<?php

namespace App\Domain\Stock\Ports;

use App\Domain\Stock\StockBalance;
use Ramsey\Uuid\UuidInterface;

interface StockBalanceRepositoryPort
{
    public function getBalanceByVariantId(UuidInterface $variantId): StockBalance;

    /** Obtém saldo garantindo que nenhuma operação concorrente altere o resultado antes da escrita. Deve ser chamado dentro de TransactionPort::run(). */
    public function getBalanceByVariantIdForUpdate(UuidInterface $variantId): StockBalance;
}
