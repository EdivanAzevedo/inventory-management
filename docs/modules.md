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
