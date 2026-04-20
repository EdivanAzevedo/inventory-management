# Módulos

## Product

Gerencia o ciclo de vida de produtos e suas variações (SKUs).

### Domain

| Classe | Tipo | Descrição |
|---|---|---|
| `Product` | Aggregate Root | Dados canônicos do produto; soft-deletable |
| `ProductVariant` | Entity | SKU com atributos (cor, tamanho, unidade) |
| `ProductType` | Enum | `PRODUTO_FINAL`, `MATERIA_PRIMA`, `INSUMO` |
| `ProductDeactivated` | Domain Event | Disparado no soft delete do produto |
| `ProductRepositoryPort` | Interface (Port) | Contrato de persistência |

### Use Cases

| Use Case | Descrição |
|---|---|
| `RegisterProductUseCase` | Cria produto com variações iniciais |
| `UpdateProductUseCase` | Atualiza nome, tipo e descrição |
| `DeactivateProductUseCase` | Soft delete; dispara `ProductDeactivated` |
| `GetProductUseCase` | Busca por ID ou lista todos |
| `AddProductVariantUseCase` | Adiciona nova variação ao produto |
| `RemoveProductVariantUseCase` | Desativa variação (soft delete) |

### Exemplo de uso

```php
$useCase = app(RegisterProductUseCase::class);

$product = $useCase->execute(new RegisterProductDTO(
    name: 'Camiseta Básica',
    type: 'PRODUTO_FINAL',
    variants: [
        new RegisterVariantDTO(sku: 'CAM-P', unit: 'UN', minimumStock: 10, size: 'P'),
        new RegisterVariantDTO(sku: 'CAM-M', unit: 'UN', minimumStock: 10, size: 'M'),
    ],
));
```

---

## Stock

Controla movimentações de estoque (entradas, saídas e estornos) com rastreabilidade completa.

### Domain

| Classe | Tipo | Descrição |
|---|---|---|
| `StockMovement` | Aggregate Root | Evento imutável de entrada, saída ou estorno |
| `StockBalance` | Read Model | Saldo calculado a partir dos `StockMovements` |
| `MovementType` | Enum | `ENTRY`, `EXIT`, `REVERSAL` |
| `StockMovementRegistered` | Domain Event | Toda entrada ou saída confirmada |
| `StockMovementReversed` | Domain Event | Estorno de movimentação |
| `StockBelowMinimumDetected` | Domain Event | Saldo abaixo do estoque mínimo |
| `StockMovementRepositoryPort` | Interface (Port) | Contrato de persistência de movimentações |
| `StockBalanceRepositoryPort` | Interface (Port) | Contrato de cálculo de saldo |

### Use Cases

| Use Case | Descrição |
|---|---|
| `RecordEntryUseCase` | Registra entrada; dispara `StockMovementRegistered` |
| `RecordExitUseCase` | Registra saída; valida saldo; dispara `StockBelowMinimumDetected` se necessário |
| `CancelMovementUseCase` | Cria `REVERSAL` compensatório; valida que saldo não fica negativo |
| `QueryStockBalanceUseCase` | Retorna saldo atual por variante |
| `ListMovementsByVariantUseCase` | Lista histórico de movimentações por variante |

### Regras de negócio

- Saída só é registrada se `saldo >= quantidade solicitada`
- `StockMovement` nunca é deletado; cancelamentos geram um novo movimento do tipo `REVERSAL`
- Estorno de uma entrada valida que o saldo resultante não ficará negativo
- `StockBelowMinimumDetected` é disparado após saída quando `novoSaldo < minimumStock`

### Exemplo de uso

```php
// Entrada
app(RecordEntryUseCase::class)->execute(new RecordEntryDTO(
    variantId: 'uuid-da-variante',
    quantity: 50,
    reason: 'Compra NF-001',
));

// Saída
app(RecordExitUseCase::class)->execute(new RecordExitDTO(
    variantId: 'uuid-da-variante',
    quantity: 10,
    reason: 'Venda pedido #123',
));

// Estorno
app(CancelMovementUseCase::class)->execute('uuid-da-movimentacao', 'Lançamento errado');
```

---

## Shared

Utilitários compartilhados entre módulos.

| Classe | Tipo | Descrição |
|---|---|---|
| `IdGeneratorPort` | Interface (Port) | Contrato de geração de ID (`Domain/Shared/Ports`) |
| `UuidV4Generator` | Adapter | Implementação com Ramsey UUID v4 (`Infrastructure/Identity`) |

Todos os use cases que criam agregados recebem `IdGeneratorPort` via injeção de dependência.
