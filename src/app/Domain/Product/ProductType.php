<?php

namespace App\Domain\Product;

enum ProductType: string
{
    case PRODUTO_FINAL  = 'PRODUTO_FINAL';
    case MATERIA_PRIMA  = 'MATERIA_PRIMA';
    case INSUMO         = 'INSUMO';
}
