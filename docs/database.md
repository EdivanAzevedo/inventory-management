# Banco de Dados

## users

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | BIGINT (PK, auto-increment) | Identificador único |
| `name` | VARCHAR(255) | Nome do usuário |
| `email` | VARCHAR(255) UNIQUE | E-mail de acesso |
| `password` | VARCHAR(255) | Hash bcrypt |
| `role` | VARCHAR(255) | `admin`, `operator` ou `viewer` — padrão `operator` |
| `email_verified_at` | TIMESTAMP (nullable) | Verificação de e-mail (não utilizada atualmente) |
| `remember_token` | VARCHAR(100) (nullable) | Token de "lembrar sessão" (web) |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |

## personal_access_tokens

Gerenciada pelo Laravel Sanctum. Armazena os tokens de API emitidos por usuário.

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | BIGINT (PK) | — |
| `tokenable_type` | VARCHAR(255) | Tipo do modelo dono do token (`UserModel`) |
| `tokenable_id` | BIGINT | ID do usuário dono do token |
| `name` | VARCHAR(255) | Nome descritivo do token (ex.: `auth-token`) |
| `token` | VARCHAR(64) UNIQUE | Hash SHA-256 do token |
| `abilities` | TEXT (nullable) | Permissões do token (não utilizado) |
| `last_used_at` | TIMESTAMP (nullable) | Último uso |
| `expires_at` | TIMESTAMP (nullable) | Expiração (não configurado) |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |

> O token em plain-text é retornado apenas uma vez no login/registro e nunca é armazenado. Logout chama `tokens()->delete()` e invalida todos os tokens do usuário.

---

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

**Índices:** `variant_id`, `referenced_movement_id`, `created_at`.

> `StockMovement` é imutável — nunca sofre UPDATE nem DELETE. Cancelamentos criam um novo registro do tipo `REVERSAL`.

### Cálculo de saldo

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
