<?php

namespace Database\Seeders;

use App\Application\Stock\CancelMovement\CancelMovementUseCase;
use App\Application\Stock\RecordEntry\RecordEntryDTO;
use App\Application\Stock\RecordEntry\RecordEntryUseCase;
use App\Application\Stock\RecordExit\RecordExitDTO;
use App\Application\Stock\RecordExit\RecordExitUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function __construct(
        private RecordEntryUseCase    $recordEntry,
        private RecordExitUseCase     $recordExit,
        private CancelMovementUseCase $cancelMovement,
    ) {}

    public function run(): void
    {
        $variants = ProductVariantModel::all()->keyBy('sku');

        // ---------------------------------------------------------------
        // Entradas iniciais de estoque
        // ---------------------------------------------------------------
        $entries = [
            // Camisetas brancas
            'CAM-P-BRC' => [50, 'Compra NF-001/2026'],
            'CAM-M-BRC' => [80, 'Compra NF-001/2026'],
            'CAM-G-BRC' => [60, 'Compra NF-001/2026'],
            // Camisetas pretas
            'CAM-P-PTO' => [40, 'Compra NF-002/2026'],
            'CAM-M-PTO' => [70, 'Compra NF-002/2026'],
            'CAM-G-PTO' => [50, 'Compra NF-002/2026'],
            // Calças jeans
            'CJS-38-AZL' => [30, 'Compra NF-003/2026'],
            'CJS-40-AZL' => [45, 'Compra NF-003/2026'],
            'CJS-42-AZL' => [25, 'Compra NF-003/2026'],
            'CJS-40-PTO' => [20, 'Compra NF-004/2026'],
            // Matéria-prima
            'TEC-ALG-BRC' => [15, 'Compra matéria-prima MP-010'],
            'TEC-ALG-PTO' => [10, 'Compra matéria-prima MP-010'],
            // Insumos
            'LIN-BRC' => [100, 'Reposição insumos INS-005'],
            'LIN-PTO' => [100, 'Reposição insumos INS-005'],
            'LIN-COL' => [50,  'Reposição insumos INS-005'],
            'BOT-BRC-100' => [30, 'Compra botões NF-006'],
            'BOT-PTO-100' => [25, 'Compra botões NF-006'],
        ];

        $movementIds = [];

        foreach ($entries as $sku => [$qty, $reason]) {
            if (! isset($variants[$sku])) continue;

            $movement = $this->recordEntry->execute(new RecordEntryDTO(
                variantId: $variants[$sku]->id,
                quantity:  $qty,
                reason:    $reason,
            ));

            $movementIds[$sku] = $movement->getId()->toString();
        }

        // ---------------------------------------------------------------
        // Saídas — vendas e consumo de produção
        // ---------------------------------------------------------------
        $exits = [
            'CAM-M-BRC' => [15, 'Venda pedido #PV-1001'],
            'CAM-G-BRC' => [8,  'Venda pedido #PV-1002'],
            'CAM-P-PTO' => [12, 'Venda pedido #PV-1003'],
            'CAM-M-PTO' => [20, 'Venda pedido #PV-1004'],
            'CJS-40-AZL' => [10, 'Venda pedido #PV-1005'],
            'CJS-42-AZL' => [5,  'Venda pedido #PV-1006'],
            'TEC-ALG-BRC' => [2, 'Consumo produção OP-201'],
            'LIN-BRC'     => [18, 'Consumo produção OP-201'],
            'LIN-PTO'     => [12, 'Consumo produção OP-202'],
            'BOT-BRC-100' => [4, 'Consumo produção OP-201'],
        ];

        $exitIds = [];

        foreach ($exits as $sku => [$qty, $reason]) {
            if (! isset($variants[$sku])) continue;

            $movement = $this->recordExit->execute(new RecordExitDTO(
                variantId: $variants[$sku]->id,
                quantity:  $qty,
                reason:    $reason,
            ));

            $exitIds[$sku] = $movement->getId()->toString();
        }

        // ---------------------------------------------------------------
        // Segunda entrada (reposição)
        // ---------------------------------------------------------------
        $reposicao = [
            'CAM-M-BRC' => [30, 'Reposição NF-010/2026'],
            'CAM-G-PTO' => [20, 'Reposição NF-010/2026'],
        ];

        foreach ($reposicao as $sku => [$qty, $reason]) {
            if (! isset($variants[$sku])) continue;

            $this->recordEntry->execute(new RecordEntryDTO(
                variantId: $variants[$sku]->id,
                quantity:  $qty,
                reason:    $reason,
            ));
        }

        // ---------------------------------------------------------------
        // Estorno — saída lançada por engano
        // ---------------------------------------------------------------
        if (isset($exitIds['CJS-42-AZL'])) {
            $this->cancelMovement->execute(
                $exitIds['CJS-42-AZL'],
                'Cancelamento pedido #PV-1006 — cliente desistiu'
            );
        }
    }
}
