# Arquitetura

O sistema segue a Arquitetura Hexagonal (Ports & Adapters) dentro do Laravel, organizada em três camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| Domain | `src/app/Domain/` | Entidades, Aggregates, Value Objects, Domain Events, Ports (interfaces) |
| Application | `src/app/Application/` | Use Cases — orquestram o domínio sem conhecer infraestrutura |
| Infrastructure | `src/app/Infrastructure/` | Adapters: Eloquent, HTTP Controllers, Notificações |

## Princípios aplicados

- **SRP** — cada Use Case é uma classe com única responsabilidade; lógica compartilhada entre use cases é extraída para serviços de Application (ex.: `MinimumStockChecker`)
- **DIP** — repositórios, dispatcher de eventos e transações são injetados via interface (Port), nunca via implementação concreta
- **OCP** — novos tipos de movimentação ou produto não alteram código existente
- **ISP** — ports têm interfaces mínimas e focadas

## Fluxo de uma requisição

```
HTTP Request
  → Controller (Infrastructure/Http)
    → UseCase (Application)
      → Domain Aggregate
      → RepositoryPort (Domain/Ports)
        → EloquentRepository (Infrastructure/Persistence)
```

## Ports compartilhados (Domain/Shared/Ports)

Todos os contratos abaixo vivem no domínio e nunca importam código de framework:

| Port | Implementação (Infrastructure) | Finalidade |
|---|---|---|
| `IdGeneratorPort` | `UuidV4Generator` | Geração de UUID; substituível por ULID/Snowflake alterando só o binding |
| `NotificationPort` | `LogNotificationAdapter` | Envio de alertas de estoque |
| `EventDispatcherPort` | `LaravelEventDispatcherAdapter` | Publicação de domain events sem acoplamento ao framework |
| `TransactionPort` | `LaravelTransactionAdapter` | Execução atômica de operações; use cases não conhecem `DB::transaction()` |

Use cases injetam `EventDispatcherPort` em vez de `Illuminate\Contracts\Events\Dispatcher` — a Application layer permanece portável entre frameworks.

## Fluxo de saída/cancelamento com atomicidade e lock pessimista

`RecordExitUseCase` e `CancelMovementUseCase` executam a gravação dentro de `TransactionPort::run()`. Dentro da transação, `getBalanceByVariantIdForUpdate()` aplica um lock no registro de `product_variants` para serializar operações concorrentes na mesma variante:

```
RecordExitUseCase / CancelMovementUseCase
  → TransactionPort::run(fn)
    → StockBalanceRepositoryPort::getBalanceByVariantIdForUpdate()  ← lock na variante
    → StockMovement::createExit() / createReversal()                ← domínio valida regras
    → StockMovementRepositoryPort::save()
  → (commit) — eventos disparados somente após commit
  → EventDispatcherPort::dispatch(StockMovementRegistered | Reversed)
  → MinimumStockChecker::check()
    → EventDispatcherPort::dispatch(StockBelowMinimumDetected)
```

**Por que os eventos são disparados fora da transação:** se disparados dentro, uma falha no commit propagaria eventos que nunca deveriam ter ocorrido. O padrão correto é publicar domain events apenas após a confirmação atômica das mudanças.

## Fluxo do alerta assíncrono de estoque mínimo

`MinimumStockChecker` (Application/Stock/Shared) concentra a regra compartilhada entre `RecordExitUseCase` e `CancelMovementUseCase`:

```
MinimumStockChecker::check(variantId, newBalance)
  → ProductVariantRepositoryPort::findById()
  → se newBalance < minimumStock:
    → EventDispatcherPort::dispatch(StockBelowMinimumDetected)
      → CheckMinimumStockHandler (ShouldQueue, fila: stock-alerts)
        → NotificationPort::sendStockAlert()
          → LogNotificationAdapter (Log::warning)
```

**Componentes envolvidos:**

| Componente | Camada | Responsabilidade |
|---|---|---|
| `MinimumStockChecker` | Application/Stock/Shared | Verifica mínimo e emite o evento; shared entre use cases |
| `StockBelowMinimumDetected` | Domain/Events | Carrega variantId, saldo atual e mínimo |
| `NotificationPort` | Domain/Shared/Ports | Interface de envio de alerta |
| `CheckMinimumStockHandler` | Application | Recebe o evento e chama o port |
| `LogNotificationAdapter` | Infrastructure/Notification | Implementação atual: grava em log |
| `AppServiceProvider` | Infrastructure | Registra o listener no event bus do Laravel |

O driver de fila padrão é `database` (tabela `jobs`). Em ambiente de teste usa `sync` (definido no `phpunit.xml`).

---

## Dívida técnica

### ✅ Resolvidas em 2026-04-22

| ID | Problema | Resolução |
|---|---|---|
| DT-02 | Use cases acoplados a `Illuminate\Contracts\Events\Dispatcher` | `EventDispatcherPort` + `LaravelEventDispatcherAdapter` |
| DT-03 | `checkMinimumStock()` duplicado em dois use cases | Extraído para `MinimumStockChecker` (Application/Stock/Shared) |
| DT-05 | FQCN `\Ramsey\Uuid\UuidInterface` sem import em `CancelMovementUseCase` | Eliminado na reescrita do use case |
| DT-07 | Race condition em saída/cancelamento sem transação e lock | `TransactionPort` + `getBalanceByVariantIdForUpdate()` |
| DT-08 | Duplo estorno não impedido em `CancelMovementUseCase` | `existsReversalFor()` verificado dentro da transação; lança `MovementAlreadyReversedException` |
| DT-12 | Falta de índice em `stock_movements.created_at` | Migration `2026_04_22_000000_add_created_at_index_to_stock_movements_table` |
| DT-13 | `WHERE {$where}` por interpolação em `EloquentStockReportRepository` | Substituído por `(? IS NULL OR coluna = ?)` totalmente parametrizado |

### 🔴 Pendentes (antes de produção)

**DT-01 — `ShouldQueue` na camada Application**

`CheckMinimumStockHandler` implementa `Illuminate\Contracts\Queue\ShouldQueue` e declara `$queue`. A Application layer não deve conhecer o mecanismo de entrega (fila).

*Correção:* Criar `Infrastructure/Events/QueuedStockAlertListener` com `ShouldQueue` que delega ao handler puro de Application.

**DT-04 — Regra de negócio na Application layer**

`newBalance < variant->getMinimumStock()` em `MinimumStockChecker` é regra de domínio. Pertence à entidade `ProductVariant` como `isBelowMinimum(int $balance): bool`.

**DT-06 — Strings literais no SQL de `EloquentStockBalanceRepository`**

`'ENTRY'`, `'EXIT'`, `'REVERSAL'` hardcoded no SQL. Se os valores do enum mudarem, o SQL quebra silenciosamente.

*Correção:* Usar `MovementType::ENTRY->value` nos bindings.

### 🟡 Pendentes (melhoria de design)

**DT-09** — `StockReport` e `StockReportEntry` estão em `Domain/Stock`; semanticamente são Read Models da camada Application.

**DT-10** — `ProductVariant` não enforça invariantes no construtor: `minimumStock` pode ser negativo, `sku`/`unit` podem ser strings vazias.

**DT-11** — `new DateTimeImmutable()` chamado diretamente nos factory methods de `StockMovement`; impossibilita controle do clock em testes sem um `ClockPort`.

---

## Troca de frontend

O backend expõe JSON via Resources (`ProductResource`, `StockMovementResource`). Controllers retornam `JsonResponse` ou `ResourceCollection`, desacoplados da camada de apresentação.

O frontend atual usa **Alpine.js + Blade** com arquitetura em duas camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| API Adapters | `resources/js/api/` | Única camada que conhece `fetch` e as URLs da API |
| Components | `resources/js/components/` | Alpine.js — camada de apresentação, trocável |

Para migrar para Vue/React, reescreve-se apenas `resources/js/components/`. A camada `api/` permanece intacta.
