# Módulos

## Auth

Gerencia autenticação (login/logout/registro) e autorização por role.

### Domain

| Classe | Tipo | Descrição |
|---|---|---|
| `User` | Aggregate Root | Usuário com id auto-increment, name, email, passwordHash e role |
| `UserRole` | Enum | `admin`, `operator`, `viewer` |
| `UserRepositoryPort` | Interface (Port) | `save`, `findById`, `findByEmail`, `existsByEmail` |
| `UserTokenPort` | Interface (Port) | `createToken`, `revokeAllTokens` |
| `PasswordHasherPort` | Interface (Port) | `hash`, `verify` — em `Domain/Shared/Ports` |

### Use Cases

| Use Case | Descrição |
|---|---|
| `RegisterUserUseCase` | Valida unicidade do e-mail, cria usuário com role `operator`, retorna token Sanctum |
| `AuthenticateUserUseCase` | Verifica credenciais, retorna token Sanctum |
| `RevokeTokenUseCase` | Revoga todos os tokens do usuário (logout) |
| `UpdateUserRoleUseCase` | Altera o role de um usuário — exclusivo para `admin` |

### Policies (Infrastructure/Http/Policies)

As policies são registradas via `Gate::policy()` no `AppServiceProvider` e controlam o acesso por role. Controllers não contêm lógica de role.

| Policy | Método | Roles permitidos |
|---|---|---|
| `ProductPolicy` | `viewAny`, `view` | todos |
| `ProductPolicy` | `create`, `update`, `addVariant` | `admin`, `operator` |
| `ProductPolicy` | `delete`, `reactivate`, `removeVariant` | `admin` |
| `StockPolicy` | `viewBalance`, `viewMovements`, `viewReport` | todos |
| `StockPolicy` | `record`, `cancel`, `transfer` | `admin`, `operator` |
| `UserPolicy` | `updateRole` | `admin` |

### Exemplo de uso

```php
// Registro
app(RegisterUserUseCase::class)->execute(new RegisterUserDTO(
    name:     'João Silva',
    email:    'joao@example.com',
    password: 'secret123',
));

// Login
app(AuthenticateUserUseCase::class)->execute(new AuthenticateUserDTO(
    email:    'joao@example.com',
    password: 'secret123',
));

// Alterar role
app(UpdateUserRoleUseCase::class)->execute(new UpdateUserRoleDTO(
    userId: 1,
    role:   'admin',
));
```

---

## Product

Gerencia o ciclo de vida de produtos e suas variações (SKUs).

### Domain

| Classe | Tipo | Descrição |
|---|---|---|
| `Product` | Aggregate Root | Dados canônicos do produto; soft-deletable |
| `ProductVariant` | Entity | SKU com atributos (cor, tamanho, unidade) |
| `ProductType` | Enum | `PRODUTO_FINAL`, `MATERIA_PRIMA`, `INSUMO` |
| `ProductDeactivated` | Domain Event | Disparado no soft delete do produto |
| `ProductReactivated` | Domain Event | Disparado ao restaurar um produto inativo |
| `ProductRepositoryPort` | Interface (Port) | Contrato de persistência |

### Use Cases

| Use Case | Descrição |
|---|---|
| `RegisterProductUseCase` | Cria produto com variações iniciais |
| `UpdateProductUseCase` | Atualiza nome, tipo e descrição |
| `DeactivateProductUseCase` | Soft delete; dispara `ProductDeactivated` |
| `ListInactiveProductsUseCase` | Lista todos os produtos inativos (soft-deleted) |
| `ReactivateProductUseCase` | Restaura produto inativo; dispara `ProductReactivated` |
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
| `StockReportRepositoryPort` | Interface (Port) | Contrato de geração de relatório consolidado |
| `StockReport` | Value Object | Envelope do relatório: período, `generated_at` e lista de entradas |
| `StockReportEntry` | Value Object | Linha do relatório: produto, variante, `total_entries`, `total_exits`, `net_balance` |

### Use Cases

| Use Case / Handler | Descrição |
|---|---|
| `RecordEntryUseCase` | Registra entrada; dispara `StockMovementRegistered` |
| `RecordExitUseCase` | Registra saída dentro de transação com lock pessimista; valida saldo; delega alerta a `MinimumStockChecker` |
| `CancelMovementUseCase` | Verifica duplo estorno; cria `REVERSAL` dentro de transação com lock; delega alerta a `MinimumStockChecker` |
| `CheckMinimumStockHandler` | Handler assíncrono do evento `StockBelowMinimumDetected`; delega para `NotificationPort` |
| `QueryStockBalanceUseCase` | Retorna saldo atual por variante |
| `ListMovementsByVariantUseCase` | Lista histórico de movimentações por variante |
| `GenerateStockReportUseCase` | Gera relatório agrupado por produto/SKU no período; aceita filtros de `product_id` e `product_type` |

### Serviços compartilhados (Application/Stock/Shared)

| Serviço | Descrição |
|---|---|
| `MinimumStockChecker` | Verifica se o novo saldo fica abaixo do mínimo e dispara `StockBelowMinimumDetected`; compartilhado entre `RecordExitUseCase` e `CancelMovementUseCase` |

### Regras de negócio

- Saída só é registrada se `saldo >= quantidade solicitada`
- `StockMovement` nunca é deletado; cancelamentos geram um novo movimento do tipo `REVERSAL`
- Cada movimentação pode ser estornada apenas uma vez; uma segunda tentativa lança `MovementAlreadyReversedException`
- Estorno de uma entrada valida que o saldo resultante não ficará negativo
- `StockBelowMinimumDetected` é disparado após saída **ou** estorno de entrada quando `novoSaldo < minimumStock`
- O evento é processado de forma assíncrona pela fila `stock-alerts` via `CheckMinimumStockHandler`
- Saídas e cancelamentos são operações atômicas: leitura de saldo e gravação do movimento ocorrem dentro da mesma transação com lock de exclusividade na variante

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

// Relatório por período
app(GenerateStockReportUseCase::class)->execute(new GenerateStockReportDTO(
    startDate:   '2026-04-01',
    endDate:     '2026-04-22',
    productType: 'PRODUTO_FINAL',
));
```

---

## Shared

Utilitários compartilhados entre módulos.

### Domain/Shared/Ports

| Port | Descrição |
|---|---|
| `ClockPort` | Contrato de leitura de data/hora; permite controle do clock em testes |
| `IdGeneratorPort` | Contrato de geração de ID |
| `NotificationPort` | Contrato de envio de alertas de estoque |
| `EventDispatcherPort` | Contrato de publicação de domain events; isola use cases do framework de eventos |
| `TransactionPort` | Contrato de execução atômica; isola use cases do mecanismo de transação de banco |
| `PasswordHasherPort` | Contrato de hash e verificação de senhas |

### Infrastructure/Adapters

| Adapter | Port implementado | Localização |
|---|---|---|
| `SystemClock` | `ClockPort` | `Infrastructure/Time` |
| `UuidV4Generator` | `IdGeneratorPort` | `Infrastructure/Identity` |
| `LogNotificationAdapter` | `NotificationPort` | `Infrastructure/Notification` |
| `LaravelEventDispatcherAdapter` | `EventDispatcherPort` | `Infrastructure/Events` |
| `LaravelTransactionAdapter` | `TransactionPort` | `Infrastructure/Transaction` |
| `BcryptPasswordHasher` | `PasswordHasherPort` | `Infrastructure/Auth` |
| `SanctumTokenAdapter` | `UserTokenPort` | `Infrastructure/Auth` |

Trocar qualquer implementação requer apenas alterar o binding no `DomainServiceProvider` — sem tocar nos use cases.
