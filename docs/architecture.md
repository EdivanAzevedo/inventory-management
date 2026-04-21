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

## Fluxo do alerta assíncrono de estoque mínimo

Quando uma saída ou cancelamento de entrada reduz o saldo abaixo do `minimum_stock` da variante, o sistema dispara um Domain Event que é processado de forma assíncrona via fila:

```
RecordExitUseCase / CancelMovementUseCase
  → dispatcher.dispatch(StockBelowMinimumDetected)
    → CheckMinimumStockHandler (ShouldQueue, fila: stock-alerts)
      → NotificationPort::sendStockAlert()
        → LogNotificationAdapter (Log::warning)
```

**Componentes envolvidos:**

| Componente | Camada | Responsabilidade |
|---|---|---|
| `StockBelowMinimumDetected` | Domain/Events | Carrega variantId, saldo atual e mínimo |
| `NotificationPort` | Domain/Shared/Ports | Interface de envio de alerta |
| `CheckMinimumStockHandler` | Application | Recebe o evento e chama o port |
| `LogNotificationAdapter` | Infrastructure/Notification | Implementação atual: grava em log |
| `AppServiceProvider` | Infrastructure | Registra o listener no event bus do Laravel |

O driver de fila padrão é `database` (tabela `jobs`). Em ambiente de teste usa `sync` (definido no `phpunit.xml`).

---

## Dívida técnica identificada

Issues encontrados na revisão de 2026-04-20. Devem ser corrigidos antes de produção.

### 🔴 DT-01 — `ShouldQueue` na camada Application

**Arquivo:** `Application/Stock/CheckMinimumStock/CheckMinimumStockHandler.php`

`CheckMinimumStockHandler` implementa `Illuminate\Contracts\Queue\ShouldQueue` e declara `$queue = 'stock-alerts'`. Isso viola o DIP e SRP: a Application layer conhece o mecanismo de entrega (fila), que é uma preocupação de infraestrutura.

**Correção planejada:** Criar `Infrastructure/Events/QueuedStockAlertListener` que implementa `ShouldQueue` e delega para o handler. O handler vira serviço Application puro.

### 🔴 DT-02 — Use cases acoplados a `Illuminate\Contracts\Events\Dispatcher`

**Arquivos:** `RecordExitUseCase`, `CancelMovementUseCase`

Os use cases injetam `Illuminate\Contracts\Events\Dispatcher` diretamente. Mesmo sendo interface, é um contrato do Laravel — troca de framework quebra a Application layer. Viola DIP.

**Correção planejada:** Definir `Domain/Shared/Ports/DomainEventDispatcherPort` e criar `Infrastructure/Events/LaravelEventDispatcherAdapter` como implementação.

### 🟡 DT-03 — Lógica `checkMinimumStock` duplicada

**Arquivos:** `RecordExitUseCase`, `CancelMovementUseCase`

Método `checkMinimumStock` idêntico em ambos os use cases. Viola DRY e SRP (cada use case acumula responsabilidade secundária de alertas).

**Correção planejada:** Extrair `Application/Stock/Shared/MinimumStockAlertService`.

### 🟡 DT-04 — Regra de negócio fora do domínio

`$newBalance < $variant->getMinimumStock()` é uma regra de domínio pura escrita na Application layer. Pertence à entidade `ProductVariant` como `isBelowMinimum(int $balance): bool`.

### 🟠 DT-05 — FQCN sem import em `CancelMovementUseCase`

Linha 56: `\Ramsey\Uuid\UuidInterface` como FQCN no corpo do método, enquanto o restante do projeto usa `use` no topo do arquivo.

### 🟠 DT-06 — Strings literais no SQL do `EloquentStockBalanceRepository`

A query usa `'ENTRY'`, `'EXIT'`, `'REVERSAL'` como strings hardcoded em vez de `MovementType::ENTRY->value`. Se os valores do enum mudarem, o SQL quebra silenciosamente.

---

## Troca de frontend

O backend expõe JSON via Resources (`ProductResource`, `StockMovementResource`). Controllers retornam `JsonResponse` ou `ResourceCollection`, desacoplados da camada de apresentação.

O frontend atual usa **Alpine.js + Blade** com arquitetura em duas camadas:

| Camada | Localização | Responsabilidade |
|---|---|---|
| API Adapters | `resources/js/api/` | Única camada que conhece `fetch` e as URLs da API |
| Components | `resources/js/components/` | Alpine.js — camada de apresentação, trocável |

Para migrar para Vue/React, reescreve-se apenas `resources/js/components/`. A camada `api/` permanece intacta.
