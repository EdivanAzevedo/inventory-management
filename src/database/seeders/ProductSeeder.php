<?php

namespace Database\Seeders;

use App\Application\Product\RegisterProduct\RegisterProductDTO;
use App\Application\Product\RegisterProduct\RegisterProductUseCase;
use App\Application\Product\RegisterProduct\RegisterVariantDTO;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function __construct(private RegisterProductUseCase $registerProduct) {}

    public function run(): void
    {
        $products = [
            [
                'name'        => 'Camiseta Básica',
                'type'        => 'PRODUTO_FINAL',
                'description' => 'Camiseta 100% algodão, corte regular',
                'variants'    => [
                    ['sku' => 'CAM-P-BRC', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'P',  'color' => 'Branco'],
                    ['sku' => 'CAM-M-BRC', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'M',  'color' => 'Branco'],
                    ['sku' => 'CAM-G-BRC', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'G',  'color' => 'Branco'],
                    ['sku' => 'CAM-P-PTO', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'P',  'color' => 'Preto'],
                    ['sku' => 'CAM-M-PTO', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'M',  'color' => 'Preto'],
                    ['sku' => 'CAM-G-PTO', 'unit' => 'UN', 'minimum_stock' => 10, 'size' => 'G',  'color' => 'Preto'],
                ],
            ],
            [
                'name'        => 'Calça Jeans Slim',
                'type'        => 'PRODUTO_FINAL',
                'description' => 'Calça jeans corte slim fit',
                'variants'    => [
                    ['sku' => 'CJS-38-AZL', 'unit' => 'UN', 'minimum_stock' => 5, 'size' => '38', 'color' => 'Azul'],
                    ['sku' => 'CJS-40-AZL', 'unit' => 'UN', 'minimum_stock' => 5, 'size' => '40', 'color' => 'Azul'],
                    ['sku' => 'CJS-42-AZL', 'unit' => 'UN', 'minimum_stock' => 5, 'size' => '42', 'color' => 'Azul'],
                    ['sku' => 'CJS-40-PTO', 'unit' => 'UN', 'minimum_stock' => 5, 'size' => '40', 'color' => 'Preto'],
                ],
            ],
            [
                'name'        => 'Tecido de Algodão',
                'type'        => 'MATERIA_PRIMA',
                'description' => 'Tecido algodão 100% — rolo de 50m',
                'variants'    => [
                    ['sku' => 'TEC-ALG-BRC', 'unit' => 'ROLO', 'minimum_stock' => 3, 'color' => 'Branco'],
                    ['sku' => 'TEC-ALG-PTO', 'unit' => 'ROLO', 'minimum_stock' => 3, 'color' => 'Preto'],
                ],
            ],
            [
                'name'        => 'Linha de Costura',
                'type'        => 'INSUMO',
                'description' => 'Linha poliéster resistente — bobina 500m',
                'variants'    => [
                    ['sku' => 'LIN-BRC', 'unit' => 'BOBINA', 'minimum_stock' => 20, 'color' => 'Branco'],
                    ['sku' => 'LIN-PTO', 'unit' => 'BOBINA', 'minimum_stock' => 20, 'color' => 'Preto'],
                    ['sku' => 'LIN-COL', 'unit' => 'BOBINA', 'minimum_stock' => 10, 'color' => 'Colorido'],
                ],
            ],
            [
                'name'        => 'Botão de Camisa',
                'type'        => 'INSUMO',
                'description' => 'Botão 4 furos — pacote com 100 unidades',
                'variants'    => [
                    ['sku' => 'BOT-BRC-100', 'unit' => 'PCT', 'minimum_stock' => 5, 'color' => 'Branco'],
                    ['sku' => 'BOT-PTO-100', 'unit' => 'PCT', 'minimum_stock' => 5, 'color' => 'Preto'],
                ],
            ],
        ];

        foreach ($products as $data) {
            $variants = array_map(
                fn ($v) => new RegisterVariantDTO(
                    sku:          $v['sku'],
                    unit:         $v['unit'],
                    minimumStock: $v['minimum_stock'],
                    color:        $v['color'] ?? null,
                    size:         $v['size'] ?? null,
                ),
                $data['variants']
            );

            $this->registerProduct->execute(new RegisterProductDTO(
                name:        $data['name'],
                type:        $data['type'],
                description: $data['description'],
                variants:    $variants,
            ));
        }
    }
}
