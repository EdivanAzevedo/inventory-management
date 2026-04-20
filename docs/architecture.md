# Arquitetura

O sistema segue a Arquitetura Hexagonal (Ports & Adapters) dentro do Laravel, organizada em três camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| Domain | `src/app/Domain/` | Entidades, Aggregates, Value Objects, Domain Events, Ports (interfaces) |
| Application | `src/app/Application/` | Use Cases — orquestram o domínio sem conhecer infraestrutura |
| Infrastructure | `src/app/Infrastructure/` | Adapters: Eloquent, HTTP Controllers, Notificações |

## Princípios aplicados

- **SRP** — cada Use Case é uma classe com única responsabilidade
- **DIP** — repositórios são injetados via interface (Port), nunca via implementação concreta
- **OCP** — novos tipos de movimentação ou produto não alteram código existente

## Fluxo de uma requisição

```
HTTP Request
  → Controller (Infrastructure/Http)
    → UseCase (Application)
      → Domain Aggregate
      → RepositoryPort (Domain/Ports)
        → EloquentRepository (Infrastructure/Persistence)
```

## IdGeneratorPort — geração de ID como Port

A geração de UUIDs é abstraída atrás de uma interface no domínio compartilhado:

```
Domain/Shared/Ports/IdGeneratorPort       ← interface pura
Infrastructure/Identity/UuidV4Generator   ← implementação com Ramsey UUID
```

Use cases que criam agregados recebem `IdGeneratorPort` via construtor. A lógica de geração nunca vaza para o domínio e pode ser substituída (ex.: ULID, Snowflake) alterando apenas o binding no `DomainServiceProvider`.

## Troca de frontend

O backend expõe JSON via Resources (`ProductResource`, `StockMovementResource`). Controllers retornam `JsonResponse` ou `ResourceCollection`, desacoplados da camada de apresentação.

O frontend atual usa **Alpine.js + Blade** com arquitetura em duas camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| API Adapters | `resources/js/api/` | Única camada que conhece `fetch` e as URLs da API |
| Components | `resources/js/components/` | Alpine.js — camada de apresentação, trocável |

Para migrar para Vue/React, reescreve-se apenas `resources/js/components/`. A camada `api/` permanece intacta.
