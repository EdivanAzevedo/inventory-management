# Arquitetura

O sistema segue a Arquitetura Hexagonal (Ports & Adapters) dentro do Laravel, organizada em três camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| Domain | `src/app/Domain/` | Entidades, Aggregates, Value Objects, Domain Events, Ports (interfaces) |
| Application | `src/app/Application/` | Use Cases — orquestram o domínio sem conhecer infraestrutura |
| Infrastructure | `src/app/Infrastructure/` | Adapters: Eloquent, HTTP Controllers, Auth, Notificações |

## Princípios aplicados

- **SRP** — cada Use Case é uma classe com única responsabilidade; lógica compartilhada entre use cases é extraída para serviços de Application (ex.: `MinimumStockChecker`)
- **DIP** — repositórios, tokens, hashing, dispatcher de eventos e transações são injetados via interface (Port), nunca via implementação concreta
- **OCP** — novos tipos de movimentação ou produto não alteram código existente
- **ISP** — ports têm interfaces mínimas e focadas

## Fluxo de uma requisição

```
HTTP Request
  → Middleware auth:sanctum (valida token Bearer)
  → Gate::authorize() (verifica Policy por role)
  → Controller (Infrastructure/Http)
    → UseCase (Application)
      → Domain Aggregate
      → Port (Domain)
        → Adapter (Infrastructure)
```

## Ports (Domain)

### Domain/Shared/Ports — contratos transversais

| Port | Implementação | Finalidade |
|---|---|---|
| `ClockPort` | `SystemClock` | Leitura de data/hora; substituível em testes |
| `IdGeneratorPort` | `UuidV4Generator` | Geração de UUID; substituível por ULID/Snowflake |
| `NotificationPort` | `LogNotificationAdapter` | Envio de alertas de estoque |
| `EventDispatcherPort` | `LaravelEventDispatcherAdapter` | Publicação de domain events sem acoplamento ao framework |
| `TransactionPort` | `LaravelTransactionAdapter` | Execução atômica; use cases não conhecem `DB::transaction()` |
| `PasswordHasherPort` | `BcryptPasswordHasher` | Hash e verificação de senhas; use cases não conhecem `Hash::make()` |

### Domain/User/Ports — contratos do módulo Auth

| Port | Implementação | Finalidade |
|---|---|---|
| `UserRepositoryPort` | `EloquentUserRepository` | Persistência do aggregate `User` |
| `UserTokenPort` | `SanctumTokenAdapter` | Emissão e revogação de tokens Sanctum; use cases não conhecem `HasApiTokens` |

## Autorização por Policies

Policies vivem em `Infrastructure/Http/Policies/` e são registradas via `Gate::policy()` no `AppServiceProvider`:

```php
Gate::policy(Product::class,      ProductPolicy::class);
Gate::policy(StockMovement::class, StockPolicy::class);
Gate::policy(User::class,          UserPolicy::class);
```

Controllers usam `Gate::authorize('acao', Classe::class)` — sem nenhuma verificação de role direta. Policies recebem o `UserModel` autenticado pelo Sanctum e verificam o role via `UserRole::from($user->role)`.

## Fluxo de saída/cancelamento com atomicidade e lock pessimista

```
RecordExitUseCase / CancelMovementUseCase
  → TransactionPort::run(fn)
    → StockBalanceRepositoryPort::getBalanceByVariantIdForUpdate()  ← lock na variante
    → StockMovement::createExit() / createReversal()                ← domínio valida regras
    → StockMovementRepositoryPort::save()
  → (commit)
  → EventDispatcherPort::dispatch(StockMovementRegistered | Reversed)
  → MinimumStockChecker::check()
    → EventDispatcherPort::dispatch(StockBelowMinimumDetected)
```

Eventos são disparados **fora** da transação: se disparados dentro, uma falha no commit propagaria eventos que nunca deveriam ter ocorrido.

## Fluxo do alerta assíncrono de estoque mínimo

```
MinimumStockChecker::check(variantId, newBalance)
  → ProductVariantRepositoryPort::findById()
  → se newBalance < minimumStock:
    → EventDispatcherPort::dispatch(StockBelowMinimumDetected)
      → QueuedStockAlertListener (Infrastructure, ShouldQueue, fila: stock-alerts)
        → CheckMinimumStockHandler (Application — sem ShouldQueue)
          → NotificationPort::sendStockAlert()
            → LogNotificationAdapter → Log::warning
```

| Componente | Camada | Responsabilidade |
|---|---|---|
| `MinimumStockChecker` | Application/Stock/Shared | Verifica mínimo e emite o evento |
| `StockBelowMinimumDetected` | Domain/Events | Carrega variantId, saldo atual e mínimo |
| `QueuedStockAlertListener` | Infrastructure/Events/Listeners | Implementa `ShouldQueue`; isola o framework da Application |
| `CheckMinimumStockHandler` | Application | Recebe o evento e chama `NotificationPort` |
| `LogNotificationAdapter` | Infrastructure/Notification | Implementação atual: grava `Log::warning` |

## Frontend

O backend expõe JSON via Resources (`ProductResource`, `StockMovementResource`, `UserResource`). Controllers retornam `JsonResponse` ou `ResourceCollection`, desacoplados da camada de apresentação.

O frontend usa **Alpine.js + Blade** com arquitetura em camadas análoga ao backend:

| Camada | Localização | Responsabilidade |
|---|---|---|
| Auth Infrastructure | `resources/js/auth/` | `token-store.js`, `user-store.js` — adapters de localStorage (SRP) |
| API Adapters | `resources/js/api/` | Única camada que conhece `fetch` e URLs da API; injeta Bearer token |
| Components | `resources/js/components/` | Alpine.js — camada de apresentação |

O `http-client.js` lê o token do `tokenStore` e o injeta em toda requisição. Resposta `401` → limpa storage e redireciona para `/login`. Para migrar para Vue/React, reescreve-se apenas `resources/js/components/`. As camadas `auth/` e `api/` permanecem intactas.
