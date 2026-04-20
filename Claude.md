# claude.md — Padrão de Documentação Laravel

> Consulte este arquivo a cada commit para manter a documentação atualizada.

---

## Estrutura de Documentação

```
README.md          # Visão geral, badges, instalação rápida, links para /docs
docs/
├── architecture.md      # Arquitetura, camadas, padrões de design
├── installation.md      # Instalação completa, .env, pré-requisitos
├── modules.md           # Módulos do sistema e responsabilidades
├── api.md               # Endpoints, autenticação, exemplos
├── database.md          # Schema, relacionamentos, migrations
└── changelog.md         # Histórico de mudanças por versão
```

---

## README.md — Estrutura Obrigatória

```markdown
# Nome do Projeto
![PHP](badge) ![Laravel](badge) ![License](badge)

Descrição objetiva em 1-2 linhas.

## Stack
## Quick Start        ← instalação em <10 linhas
## Documentation      ← links para /docs
## License
```

---

## O que atualizar a cada commit

| Mudança feita             | Arquivo a atualizar              |
|---------------------------|----------------------------------|
| Nova rota ou endpoint     | `docs/api.md`                    |
| Nova migration ou model   | `docs/database.md`               |
| Novo módulo ou use case   | `docs/modules.md`                |
| Mudança de arquitetura    | `docs/architecture.md`           |
| Qualquer mudança funcional| `docs/changelog.md`              |
| Novo pré-requisito        | `docs/installation.md`           |

---

## Changelog — Formato Obrigatório (Keep a Changelog)

```markdown
## [1.2.0] - 2025-04-20
### Added
- Registro de entrada de estoque por variante de produto

### Changed
- Refatoração do use case `RecordExit` para validar saldo mínimo

### Fixed
- Correção no cálculo de saldo ao cancelar movimentação
```

> Use as seções: `Added` · `Changed` · `Fixed` · `Removed` · `Security`

---

## Regras Gerais

- Escreva documentação em **português** (este projeto) ou inglês — nunca misture
- Seja objetivo: 1 parágrafo por conceito, sem texto redundante
- Exemplos de código são obrigatórios em `api.md` e `modules.md`
- Nunca documente algo que ainda não foi implementado
