# Instalação

## Pré-requisitos

- PHP 8.3+
- Composer 2.x
- Node.js 18+ e npm
- MySQL 8.0+

## Passos

```bash
git clone https://github.com/EdivanAzevedo/inventory-management.git
cd inventory-management/src

composer install
npm install
cp .env.example .env
```

Configure as variáveis no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

```bash
php artisan key:generate
php artisan migrate
```

## Ambiente de desenvolvimento

Inicia Laravel, Vite, queue listener e log tail em paralelo:

```bash
composer run dev
```

| Serviço | URL |
|---|---|
| Aplicação | http://localhost:8000 |
| API | http://localhost:8000/api |
| Vite (assets) | http://localhost:5173 |
