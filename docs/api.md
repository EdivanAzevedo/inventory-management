# API

Base URL: `http://localhost:8000/api`

## Autenticação

Todos os endpoints — exceto `POST /auth/register` e `POST /auth/login` — exigem o header:

```
Authorization: Bearer <token>
```

### Registrar usuário
`POST /auth/register`

**Body**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Resposta 201**
```json
{
  "data": { "name": "João Silva", "email": "joao@example.com", "role": "operator" },
  "token": "1|abc123..."
}
```

> Usuários criados via registro recebem o role `operator` por padrão. Apenas um `admin` pode promovê-los via `PUT /users/{id}/role`.

### Login
`POST /auth/login`

**Body**
```json
{ "email": "admin@example.com", "password": "password" }
```

**Resposta 200**
```json
{
  "data": { "name": "Administrador", "email": "admin@example.com", "role": "admin" },
  "token": "2|xyz456..."
}
```

**Resposta 401** — credenciais inválidas.

### Logout
`POST /auth/logout` *(requer autenticação)*

**Resposta 200**
```json
{ "message": "Logout realizado com sucesso." }
```

> Revoga **todos** os tokens do usuário no servidor.

---

## Usuários

### Alterar role de usuário
`PUT /users/{id}/role` *(somente `admin`)*

**Body**
```json
{ "role": "admin" }
```

Valores aceitos: `admin`, `operator`, `viewer`.

**Resposta 200**
```json
{
  "data": { "id": 1, "name": "João Silva", "email": "joao@example.com", "role": "admin" }
}
```

**Resposta 403** — usuário autenticado não é `admin`.
**Resposta 404** — usuário não encontrado.

---

## Produtos

### Listar produtos ativos
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

### Listar produtos inativos
`GET /products/inactive` *(somente `admin`)*

**Resposta 200** — mesma estrutura de `GET /products`, retorna apenas produtos com `active: false`.

### Buscar produto
`GET /products/{id}`

**Resposta 200** — objeto do produto com variações.

### Criar produto
`POST /products` *(`admin` ou `operator`)*

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

### Atualizar produto
`PUT /products/{id}` *(`admin` ou `operator`)*

**Body**
```json
{ "name": "Novo Nome", "type": "MATERIA_PRIMA", "description": null }
```

**Resposta 200** — objeto atualizado.

### Desativar produto
`DELETE /products/{id}` *(somente `admin`)*

**Resposta 204** — sem corpo.

### Reativar produto
`POST /products/{id}/reactivate` *(somente `admin`)*

**Resposta 204** — sem corpo.

### Adicionar variação
`POST /products/{productId}/variants` *(`admin` ou `operator`)*

**Body**
```json
{ "sku": "CAM-G", "unit": "UN", "minimum_stock": 5, "color": "Azul", "size": "G" }
```

**Resposta 201** — objeto da variação criada.

### Remover variação
`DELETE /products/{productId}/variants/{variantId}` *(somente `admin`)*

**Resposta 204** — sem corpo.

---

## Estoque

### Registrar entrada
`POST /stock/entries` *(`admin` ou `operator`)*

**Body**
```json
{ "variant_id": "uuid", "quantity": 50, "reason": "Compra NF-001" }
```

**Resposta 201** — objeto da movimentação criada.

### Registrar saída
`POST /stock/exits` *(`admin` ou `operator`)*

**Body**
```json
{ "variant_id": "uuid", "quantity": 10, "reason": "Venda pedido #123" }
```

**Resposta 201** — objeto da movimentação.
**Resposta 422** — saldo insuficiente.

### Estornar movimentação
`POST /stock/movements/{id}/cancel` *(`admin` ou `operator`)*

**Body**
```json
{ "reason": "Lançamento incorreto" }
```

**Resposta 201** — objeto do estorno (`type: REVERSAL`).

### Transferir estoque entre variantes
`POST /stock/transfers` *(`admin` ou `operator`)*

Operação atômica: registra saída da variante de origem e entrada na variante de destino dentro de uma única transação. Se a saída falhar (saldo insuficiente), a entrada não é registrada.

**Body**
```json
{
  "from_variant_id": "uuid-origem",
  "to_variant_id":   "uuid-destino",
  "quantity":        20,
  "reason":          "Redistribuição entre armazéns"
}
```

**Resposta 201**
```json
{
  "data": {
    "exit":  { "id": "uuid", "type": "EXIT",  "quantity": 20, ... },
    "entry": { "id": "uuid", "type": "ENTRY", "quantity": 20, ... }
  }
}
```

**Resposta 422** — saldo insuficiente na variante de origem.

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

**Exemplo**
```
GET /api/stock/report?start_date=2026-04-01&end_date=2026-04-22&product_type=PRODUTO_FINAL
```

**Resposta 200**
```json
{
  "data": {
    "period": { "start": "2026-04-01", "end": "2026-04-22" },
    "generated_at": "2026-04-22 15:30:00",
    "items": [
      {
        "product": { "id": "uuid", "name": "Camiseta Básica", "type": "PRODUTO_FINAL" },
        "variant": { "id": "uuid", "sku": "CAM-P", "color": null, "size": "P", "unit": "UN" },
        "total_entries": 100,
        "total_exits": 30,
        "net_balance": 70
      }
    ]
  }
}
```

---

## Erros comuns

| Código | Situação |
|---|---|
| `401` | Token ausente, inválido ou expirado |
| `403` | Usuário autenticado não possui permissão para a ação |
| `404` | Recurso não encontrado |
| `409` | Conflito — ex.: e-mail já cadastrado |
| `422` | Regra de negócio violada — ex.: saldo insuficiente, dados inválidos |
