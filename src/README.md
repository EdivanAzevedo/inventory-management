# Inventory Management

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13.5-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

Sistema de gerenciamento de estoque e produtos construído com Laravel 13.5 e arquitetura hexagonal (Clean Architecture).

## Stack

- **Backend:** PHP 8.3 · Laravel 13.5
- **Banco de dados:** MySQL
- **Frontend:** Alpine.js · Blade · Tailwind CSS
- **Arquitetura:** Hexagonal (Domain / Application / Infrastructure)

## Quick Start

```bash
git clone <repo>
cd inventory-management/src

composer install
cp .env.example .env
php artisan key:generate

# Configure DB_* no .env, depois:
php artisan migrate
npm install && npm run build

php artisan serve
```

Acesse `http://localhost:8000`.

## Documentation

| Arquivo | Conteúdo |
|---|---|
| [docs/architecture.md](../docs/architecture.md) | Arquitetura hexagonal, camadas e padrões |
| [docs/installation.md](../docs/installation.md) | Pré-requisitos e instalação completa |
| [docs/modules.md](../docs/modules.md) | Módulos do sistema e casos de uso |
| [docs/api.md](../docs/api.md) | Endpoints, exemplos de request/response |
| [docs/database.md](../docs/database.md) | Schema, relacionamentos e migrations |
| [docs/changelog.md](../docs/changelog.md) | Histórico de versões |

## License

MIT
