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

## Troca de frontend

O backend expõe JSON via `ProductResource`. Controllers retornam `JsonResponse` ou `ResourceCollection`, desacoplados do Blade. Para migrar para Vue/React/Inertia, basta criar novos controllers ou adaptar as respostas sem tocar em Domain ou Application.
