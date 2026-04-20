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
