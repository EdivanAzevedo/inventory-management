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

### Desativar produto
`DELETE /products/{id}`

**Resposta 204** — sem corpo.

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
