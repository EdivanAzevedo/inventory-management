# Changelog

## [0.3.0] - 2026-04-20

### Added
- Alerta assíncrono de estoque mínimo: `CheckMinimumStockHandler` (Application) processa `StockBelowMinimumDetected` via fila `stock-alerts`
- Port `NotificationPort` em `Domain/Shared/Ports` com implementação `LogNotificationAdapter` em Infrastructure
- 5 testes de feature em `StockAlertTest`: disparo do evento, ausência de evento, cancelamento de entrada, configuração do handler e delegação ao `NotificationPort`
- Registro do listener em `AppServiceProvider`: `Event::listen(StockBelowMinimumDetected::class, CheckMinimumStockHandler::class)`

### Changed
- `CancelMovementUseCase`: ao estornar uma movimentação do tipo `ENTRY`, agora verifica se o saldo resultante fica abaixo do mínimo e dispara `StockBelowMinimumDetected`
- `docs/architecture.md`: documentado o fluxo assíncrono de alerta e seção de dívida técnica (DT-01 a DT-06)
- `docs/modules.md`: `CheckMinimumStockHandler`, `NotificationPort` e `LogNotificationAdapter` adicionados; descrições de `CancelMovement` e regras de negócio atualizadas

### Debt
- **DT-01** `CheckMinimumStockHandler` implementa `ShouldQueue` na Application layer (DIP/SRP)
- **DT-02** Use cases `RecordExit` e `CancelMovement` acoplados a `Illuminate\Contracts\Events\Dispatcher` (DIP)
- **DT-03** Método `checkMinimumStock` duplicado em `RecordExitUseCase` e `CancelMovementUseCase` (DRY/SRP)
- **DT-04** Comparação `$newBalance < minimumStock` na Application layer em vez de `ProductVariant::isBelowMinimum()`
- **DT-05** FQCN `\Ramsey\Uuid\UuidInterface` sem import em `CancelMovementUseCase:56`
- **DT-06** Strings `'ENTRY'`/`'EXIT'`/`'REVERSAL'` hardcoded no SQL de `EloquentStockBalanceRepository`

## [0.2.0] - 2026-04-20

### Added
- Módulo Stock: aggregate `StockMovement`, read model `StockBalance`, enum `MovementType`
- Domain events: `StockMovementRegistered`, `StockMovementReversed`, `StockBelowMinimumDetected`
- Ports: `StockMovementRepositoryPort`, `StockBalanceRepositoryPort`, `ProductVariantRepositoryPort`
- Port compartilhado `IdGeneratorPort` em `Domain/Shared/Ports` com implementação `UuidV4Generator`
- Use cases: `RecordEntry`, `RecordExit`, `CancelMovement`, `QueryStockBalance`, `ListMovementsByVariant`
- Migration `stock_movements` com FK para `product_variants` e auto-referência para REVERSAL
- API REST: 5 novos endpoints de estoque
- Frontend: Alpine.js + Blade com páginas de Produtos, Detalhe de Produto e Dashboard de Estoque
- Camada `resources/js/api/` (http-client, product-api, stock-api) — única camada com conhecimento de fetch/URLs

### Changed
- Use cases `RegisterProduct` e `AddProductVariant` passaram a injetar `IdGeneratorPort` (DIP)
- Use cases de Stock gerados já com `IdGeneratorPort` em vez de `Uuid::uuid4()` direto

## [0.1.0] - 2026-04-20

### Added
- Inicialização do projeto Laravel 13.5 com PHP 8.3 e MySQL
- Estrutura hexagonal: camadas Domain, Application e Infrastructure
- Módulo Product: aggregate `Product`, entity `ProductVariant`, enum `ProductType`
- Domain event `ProductDeactivated`
- Port `ProductRepositoryPort` e implementação `EloquentProductRepository`
- Use cases: `RegisterProduct`, `UpdateProduct`, `DeactivateProduct`, `GetProduct`, `AddProductVariant`, `RemoveProductVariant`
- Migrations: `products` e `product_variants`
- API REST: 7 endpoints para gerenciamento de produtos e variações
- `DomainServiceProvider` com binding de repositório via interface
