ANTES DE ALTERAR ALGO NO PROJETO VOCÊ DEVERÁ ENTENDER O CONTEXTO E O QUE EU FALEI, CASO TENHA DUVIDAS PERGUNTE

# PRD — Sistema de Gerenciamento de Estoque

**Versão:** 1.0 | **Status:** Draft | **Stack:** PHP · Laravel (estrutura Hexagonal/Clean)

---

## 1. Visão Geral

Sistema responsável por controlar o ciclo de vida de produtos e suas variações (SKUs), registrar movimentações de estoque com rastreabilidade completa e emitir alertas de reposição, suportando estoques mistos (produtos finais, matéria-prima e insumos internos).

---

## 2. Princípios Arquiteturais

| Camada | Responsabilidade |
|---|---|
| `Domain/` | Entidades, Value Objects, Domain Events, interfaces de repositório (Ports) |
| `Application/` | Use Cases (orquestram domínio, sem conhecer infraestrutura) |
| `Infrastructure/` | Adapters: Eloquent, Queue, Mail, Controllers HTTP |

> **SOLID aplicado:** cada Use Case é uma classe com única responsabilidade (SRP). Repositórios são injetados via interface (DIP). Novos tipos de movimentação não alteram código existente (OCP).

---

## 3. Domínio Principal

### Agregados e Entidades

- **`Product`** *(Aggregate Root)* — dados canônicos do produto; soft-deletable.
- **`ProductVariant`** *(Entity dentro de Product)* — SKU com atributos (cor, tamanho, unidade); gerenciado pelo próprio aggregate.
- **`StockMovement`** *(Aggregate Root)* — evento imutável de entrada, saída ou estorno (`REVERSAL`). Cancela via compensação, nunca por deleção.
- **`StockBalance`** *(Read Model)* — projeção calculada a partir dos `StockMovements`.

### Domain Events

| Evento | Disparado quando |
|---|---|
| `StockMovementRegistered` | Toda entrada ou saída confirmada |
| `StockMovementReversed` | Estorno de movimentação |
| `StockBelowMinimumDetected` | Saldo cai abaixo do estoque mínimo definido |
| `ProductDeactivated` | Soft delete de produto |

---

## 4. Casos de Uso (Application Layer)

### Produto & Variações
- `RegisterProduct` — cria produto com suas variações iniciais
- `UpdateProduct` — edita dados do produto e/ou variações existentes
- `DeactivateProduct` — soft delete; preserva histórico de movimentações
- `GetProduct` — consulta produto com variações e saldo atual
- `AddProductVariant` / `RemoveProductVariant` — gerencia variações isoladamente

### Movimentação
- `RecordEntry` — registra entrada de estoque para uma variante
- `RecordExit` — registra saída; valida saldo disponível
- `CancelMovement` — cria `StockMovement` do tipo `REVERSAL` referenciando o original
- `TransferStock` — gera saída + entrada compensatória (operação atômica)

### Consulta & Alertas
- `QueryStockBalance` — retorna saldo por variante
- `CheckMinimumStock` — handler do evento `StockBelowMinimumDetected`
- `GenerateStockReport` — relatório consolidado por produto/categoria/período

---

## 5. Ports (Interfaces do Domínio)

```
ProductRepositoryPort       → salvar, buscar, listar, desativar
StockMovementRepositoryPort → salvar, buscar por referência
StockBalanceRepositoryPort  → calcular saldo por variante
NotificationPort            → notificar estoque abaixo do mínimo
```

---

## 6. Regras de Negócio

- Saída só é registrada se `saldo >= quantidade solicitada`
- `StockMovement` nunca é deletado; cancelamentos geram `REVERSAL`
- `Product` com movimentações só pode ser desativado (nunca deletado)
- `StockBelowMinimumDetected` é disparado de forma assíncrona via queue
- Tipos de estoque (`PRODUTO_FINAL`, `MATERIA_PRIMA`, `INSUMO`) não alteram regras de movimentação (OCP)

---

## 7. Estrutura de Pastas (Laravel Hexagonal)

```
app/
├── Domain/
│   ├── Product/          # Aggregate, Variants, Events
│   └── Stock/            # StockMovement, StockBalance, Ports
├── Application/
│   ├── Product/          # Use Cases de produto
│   └── Stock/            # Use Cases de movimentação e consulta
└── Infrastructure/
    ├── Persistence/      # Eloquent Adapters (implementam os Ports)
    ├── Http/             # Controllers, Requests, Resources
    └── Notification/     # Adapter de e-mail/webhook
```

---

## 8. Critérios de Aceite do MVP

- [ ] CRUD completo de produtos com variações (SKUs)
- [ ] Registro de entrada e saída com validação de saldo
- [ ] Cancelamento via estorno auditável
- [ ] Alerta assíncrono de estoque mínimo
- [ ] Consulta de saldo por variante
- [ ] Relatório básico por período
