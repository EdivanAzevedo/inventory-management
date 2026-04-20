# Banco de Dados

## products

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único |
| `name` | VARCHAR(255) | Nome do produto |
| `type` | ENUM | `PRODUTO_FINAL`, `MATERIA_PRIMA`, `INSUMO` |
| `description` | TEXT (nullable) | Descrição opcional |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |
| `deleted_at` | TIMESTAMP (nullable) | Soft delete |

## product_variants

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único |
| `product_id` | UUID (FK → products) | Produto pai |
| `sku` | VARCHAR(100) UNIQUE | Código de referência |
| `unit` | VARCHAR(20) | Unidade (UN, KG, L…) |
| `minimum_stock` | INT UNSIGNED | Estoque mínimo para alerta |
| `color` | VARCHAR(50) (nullable) | Atributo cor |
| `size` | VARCHAR(50) (nullable) | Atributo tamanho |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |
| `deleted_at` | TIMESTAMP (nullable) | Soft delete |

## stock_movements

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | UUID (PK) | Identificador único |
| `variant_id` | UUID (FK → product_variants) | Variante movimentada |
| `type` | ENUM | `ENTRY`, `EXIT`, `REVERSAL` |
| `quantity` | INT UNSIGNED | Quantidade (sempre positivo) |
| `reason` | VARCHAR(255) (nullable) | Motivo opcional |
| `referenced_movement_id` | UUID (FK → stock_movements, nullable) | Aponta para o original em `REVERSAL` |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |

> `StockMovement` é imutável — nunca sofre UPDATE nem DELETE. Cancelamentos criam um novo registro do tipo `REVERSAL`.

### Cálculo de saldo

O saldo é calculado via SQL com `LEFT JOIN` para tratar o tipo `REVERSAL`:

```sql
SELECT COALESCE(SUM(
    CASE
        WHEN sm.type = 'ENTRY'    THEN  sm.quantity
        WHEN sm.type = 'EXIT'     THEN -sm.quantity
        WHEN sm.type = 'REVERSAL' THEN
            CASE WHEN orig.type = 'ENTRY' THEN -sm.quantity
                 ELSE sm.quantity END
    END
), 0) AS balance
FROM stock_movements sm
LEFT JOIN stock_movements orig ON sm.referenced_movement_id = orig.id
WHERE sm.variant_id = ?
```
