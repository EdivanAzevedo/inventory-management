# Changelog

## [0.6.0] - 2026-04-22

### Added
- Port `EventDispatcherPort` em `Domain/Shared/Ports` — contrato de publicação de domain events sem acoplamento ao framework
- Adapter `LaravelEventDispatcherAdapter` em `Infrastructure/Events` — implementa `EventDispatcherPort` delegando para `Illuminate\Contracts\Events\Dispatcher`
- Port `TransactionPort` em `Domain/Shared/Ports` — contrato de execução atômica; use cases não conhecem `DB::transaction()`
- Adapter `LaravelTransactionAdapter` em `Infrastructure/Transaction` — implementa `TransactionPort` com `DB::transaction()`
- Serviço `MinimumStockChecker` em `Application/Stock/Shared` — lógica de verificação de estoque mínimo extraída dos use cases; elimina duplicação e concentra a responsabilidade
- Método `StockBalanceRepositoryPort::getBalanceByVariantIdForUpdate()` — obtém saldo com garantia de isolamento para operações concorrentes; implementado com lock pessimista na variante
- Método `StockMovementRepositoryPort::existsReversalFor(UuidInterface $originalId): bool` — verifica se já existe estorno para uma movimentação
- Exceção `MovementAlreadyReversedException` em `Domain/Stock/Exceptions` — lançada ao tentar estornar uma movimentação já estornada
- Migration `2026_04_22_000000_add_created_at_index_to_stock_movements_table` — índice em `stock_movements.created_at`
- Bindings de `EventDispatcherPort` e `TransactionPort` no `DomainServiceProvider`

### Changed
- `RecordEntryUseCase`, `RecordExitUseCase`, `CancelMovementUseCase` — substituem `Illuminate\Contracts\Events\Dispatcher` por `EventDispatcherPort` (DT-02 resolvida)
- `RecordExitUseCase` — operação de leitura de saldo + gravação de movimento agora executa dentro de `TransactionPort::run()` com lock pessimista (DT-07 resolvida); `checkMinimumStock()` privado removido e substituído por `MinimumStockChecker` (DT-03 resolvida)
- `CancelMovementUseCase` — mesma atomicidade da saída + verificação de duplo estorno dentro da transação (DT-07 e DT-08 resolvidas); FQCN sem import eliminado (DT-05 resolvida); `checkMinimumStock()` privado removido
- `EloquentStockReportRepository` — cláusula `WHERE` reescrita com `(? IS NULL OR coluna = ?)` totalmente parametrizado, eliminando interpolação de string (DT-13 resolvida)

### Fixed
- Race condition em saída e cancelamento: duas requisições concorrentes não conseguem mais ultrapassar o saldo disponível (DT-07)
- Duplo estorno: `CancelMovementUseCase` rejeita segunda tentativa de estorno do mesmo movimento (DT-08)

## [0.5.0] - 2026-04-22

### Added
- Domain value objects `StockReport` e `StockReportEntry` em `Domain/Stock`
- Port `StockReportRepositoryPort` em `Domain/Stock/Ports` — contrato de geração de relatório
- Use case `GenerateStockReportUseCase` com DTO `GenerateStockReportDTO` — filtros por período, produto e tipo
- Adapter `EloquentStockReportRepository` — query única que calcula `total_entries`, `total_exits` e `net_balance` (até `end_date`) via SQL com CASE/COALESCE
- `StockReportRequest` com validação de datas (`date_format:Y-m-d`, `gte:start_date`) e `Rule::enum(ProductType::class)` para tipo
- Resources `StockReportResource` e `StockReportEntryResource`
- `StockReportController` (single-action `__invoke`) injetando apenas o use case
- Endpoint `GET /api/stock/report` registrado no grupo `stock`
- Binding `StockReportRepositoryPort → EloquentStockReportRepository` em `DomainServiceProvider`

### Debt (identificada na revisão arquitetural desta sessão)
- **DT-07** Race condition crítica em `RecordExitUseCase` e `CancelMovementUseCase`: leitura de saldo e gravação do movimento não estão dentro de uma transação com lock pessimista — duas requisições concorrentes podem ultrapassar o saldo disponível
- **DT-08** `CancelMovementUseCase` não verifica se o movimento já foi estornado anteriormente — permite criar múltiplos `REVERSAL` para o mesmo movimento original
- **DT-09** `StockReport` e `StockReportEntry` são read models/DTOs colocados na camada Domain; semanticamente pertencem à camada Application
- **DT-10** `ProductVariant` não enforça invariantes no construtor: `minimumStock` pode ser negativo e `sku`/`unit` podem ser strings vazias — regras de negócio estão apenas no `FormRequest` HTTP
- **DT-11** `new DateTimeImmutable()` chamado diretamente nos factory methods de `StockMovement` — impossibilita controle do clock em testes sem um `ClockPort`
- **DT-12** Coluna `stock_movements.created_at` não possui índice; queries do relatório filtram e agrupam por essa coluna causando full scan
- **DT-13** `EloquentStockReportRepository` constrói a cláusula `WHERE` via interpolação de string (`WHERE {$where}`) — estruturalmente seguro hoje mas frágil; migrar para query builder com `->when()`

## [0.4.0] - 2026-04-20

### Added
- Domain event `ProductReactivated` (simétrico a `ProductDeactivated`)
- Método `Product::reactivate()` na entidade de domínio
- Port `ProductRepositoryPort::findInactive()` — contrato para listar produtos inativos
- Use case `ListInactiveProductsUseCase` — lista todos os produtos com soft delete ativo
- Use case `ReactivateProductUseCase` — restaura produto inativo e dispara `ProductReactivated`
- Endpoint `GET /api/products/inactive` — retorna produtos inativos
- Endpoint `POST /api/products/{id}/reactivate` — reativa produto pelo ID
- Frontend: abas "Ativos / Inativos" na listagem de produtos
- Frontend: botão "Reativar" exibido para produtos inativos
- `productApi.listInactive()` e `productApi.reactivate()` em `product-api.js`

### Changed
- `EloquentProductRepository::findById()` passa a usar `withTrashed()` para localizar produtos inativos durante a reativação
- `EloquentProductRepository::save()` corrigido: `deleted_at` é definido como `null` ao reativar (antes só era setado ao desativar)
- README reescrito seguindo estrutura do `Claude.md`
- `docs/api.md`: dois novos endpoints documentados
- `docs/modules.md`: `ProductReactivated`, `ListInactiveProductsUseCase` e `ReactivateProductUseCase` adicionados

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
