# Inventory Management
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php) ![Laravel](https://img.shields.io/badge/Laravel-13.5-FF2D20?logo=laravel) ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql) ![License](https://img.shields.io/badge/License-MIT-green)

Sistema de gerenciamento de estoque com controle de produtos, variações (SKUs) e movimentações com rastreabilidade completa.

## Stack

- **Backend:** PHP 8.3 + Laravel 13.5
- **Banco de dados:** MySQL 8.0
- **Arquitetura:** Hexagonal / Clean Architecture (Domain · Application · Infrastructure)
- **Frontend:** Blade (substituível por outro framework sem alteração no backend)
- **Testes:** PHPUnit

## Quick Start

```bash
git clone https://github.com/EdivanAzevedo/inventory-management.git
cd inventory-management/src

composer install
cp .env.example .env
# Configure DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env

php artisan key:generate
php artisan migrate
php artisan serve
```

## Documentation

- [Arquitetura](docs/architecture.md)
- [Instalação](docs/installation.md)
- [Módulos](docs/modules.md)
- [API](docs/api.md)
- [Banco de dados](docs/database.md)
- [Changelog](docs/changelog.md)

## License

MIT
