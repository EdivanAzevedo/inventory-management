# Changelog

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
