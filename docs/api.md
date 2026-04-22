# API

Base URL: `http://localhost:8000/api`

## Produtos

### Listar produtos
`GET /products`

**Resposta 200**
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Camiseta Básica",
      "type": "PRODUTO_FINAL",
      "description": null,
      "active": true,
      "variants": [...]
    }
  ]
}
```

### Criar produto
`POST /products`

**Body**
```json
{
  "name": "Camiseta Básica",
  "type": "PRODUTO_FINAL",
  "description": "Opcional",
  "variants": [
    { "sku": "CAM-P", "unit": "UN", "minimum_stock": 10, "size": "P" }
  ]
}
```
**Resposta 201** — objeto do produto criado.

### Buscar produto
`GET /products/{id}`

**Resposta 200** — objeto do produto com variações.

### Atualizar produto
`PUT /products/{id}`

**Body**
```json
{ "name": "Novo Nome", "type": "MATERIA_PRIMA", "description": null }
```
**Resposta 200** — objeto atualizado.

### Listar produtos inativos
`GET /products/inactive`

**Resposta 200** — mesma estrutura de `GET /products`, retorna apenas produtos com `active: false`.

### Desativar produto
`DELETE /products/{id}`

**Resposta 204** — sem corpo.

### Reativar produto
`POST /products/{id}/reactivate`

**Resposta 204** — sem corpo. Restaura o produto (limpa `deleted_at`) e dispara `ProductReactivated`.

## Variações

### Adicionar variação
`POST /products/{productId}/variants`

**Body**
```json
{ "sku": "CAM-G", "unit": "UN", "minimum_stock": 5, "color": "Azul", "size": "G" }
```
**Resposta 201** — objeto da variação criada.

### Remover variação
`DELETE /products/{productId}/variants/{variantId}`

**Resposta 204** — sem corpo.

---

## Estoque

### Registrar entrada
`POST /stock/entries`

**Body**
```json
{ "variant_id": "uuid", "quantity": 50, "reason": "Compra NF-001" }
```
**Resposta 201** — objeto da movimentação criada.

### Registrar saída
`POST /stock/exits`

**Body**
```json
{ "variant_id": "uuid", "quantity": 10, "reason": "Venda pedido #123" }
```
**Resposta 201** — objeto da movimentação criada.
**Resposta 500** — se saldo insuficiente (`DomainException`).

### Estornar movimentação
`POST /stock/movements/{id}/cancel`

**Body**
```json
{ "reason": "Lançamento incorreto" }
```
**Resposta 201** — objeto do estorno (`type: REVERSAL`).

### Consultar saldo
`GET /stock/balance/{variantId}`

**Resposta 200**
```json
{ "data": { "variant_id": "uuid", "quantity": 40 } }
```

### Listar movimentações da variante
`GET /stock/movements/{variantId}`

**Resposta 200**
```json
{
  "data": [
    {
      "id": "uuid",
      "variant_id": "uuid",
      "type": "ENTRY",
      "quantity": 50,
      "reason": "Compra NF-001",
      "referenced_movement_id": null,
      "created_at": "2026-04-20 21:00:00"
    }
  ]
}
```

### Relatório de estoque por período
`GET /stock/report`

**Query params**

| Parâmetro | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `start_date` | `Y-m-d` | sim | Início do período |
| `end_date` | `Y-m-d` | sim | Fim do período (>= start_date) |
| `product_id` | UUID | não | Filtra por produto específico |
| `product_type` | string | não | `PRODUTO_FINAL`, `MATERIA_PRIMA` ou `INSUMO` |

**Exemplo de requisição**
```
GET /api/stock/report?start_date=2026-04-01&end_date=2026-04-22&product_type=PRODUTO_FINAL
```

**Resposta 200**
```json
{
  "data": {
    "period": {
      "start": "2026-04-01",
      "end": "2026-04-22"
    },
    "generated_at": "2026-04-22 15:30:00",
    "items": [
      {
        "product": {
          "id": "uuid",
          "name": "Camiseta Básica",
          "type": "PRODUTO_FINAL"
        },
        "variant": {
          "id": "uuid",
          "sku": "CAM-P",
          "color": null,
          "size": "P",
          "unit": "UN"
        },
        "total_entries": 100,
        "total_exits": 30,
        "net_balance": 70
      }
    ]
  }
}
```

> `total_entries` e `total_exits` contabilizam apenas movimentos do tipo `ENTRY` e `EXIT` dentro do período informado.
> `net_balance` reflete o saldo acumulado considerando **todos** os movimentos (incluindo `REVERSAL`) até `end_date`.
