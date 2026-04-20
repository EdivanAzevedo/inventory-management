# Inventory Management
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php) ![Laravel](https://img.shields.io/badge/Laravel-13.5-FF2D20?logo=laravel) ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql) ![License](https://img.shields.io/badge/License-MIT-green)

Sistema de gerenciamento de estoque com controle de produtos, variações (SKUs) e movimentações com rastreabilidade completa.

## Stack

- **Backend:** PHP 8.3 + Laravel 13.5
- **Banco de dados:** MySQL 8.0
- **Arquitetura:** Hexagonal / Clean Architecture (Domain · Application · Infrastructure)
- **Frontend:** Blade (substituível por outro framework sem alteração no backend)
- **Testes:** PHPUnit

## Instalação

```bash
git clone https://github.com/EdivanAzevedo/inventory-management.git
cd inventory-management/src
composer install && npm install
cp .env.example .env          # configure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate
composer run dev              # sobe Laravel :8000, Vite :5173, queue e logs
```

## Documentação

- [Arquitetura](docs/architecture.md)
- [Instalação detalhada](docs/installation.md)
- [Módulos](docs/modules.md)
- [API](docs/api.md)
- [Banco de dados](docs/database.md)
- [Changelog](docs/changelog.md)

## Licença

MIT
