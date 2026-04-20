# Changelog

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
