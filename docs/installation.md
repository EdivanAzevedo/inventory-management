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
php artisan migrate --seed
```

O `--seed` cria as tabelas e popula:
- **Usuários** de exemplo com roles distintos (veja abaixo)
- **Produtos** com variações de exemplo
- **Movimentações** de estoque de exemplo

## Ambiente de desenvolvimento

```bash
composer run dev
```

Inicia em paralelo: Laravel `:8000`, Vite `:5173`, queue listener e log tail.

| Serviço | URL |
|---|---|
| Aplicação | http://localhost:8000 |
| API | http://localhost:8000/api |
| Vite (assets) | http://localhost:5173 |

Acesse http://localhost:8000 — o sistema redireciona automaticamente para `/login`.

## Usuários de exemplo

Criados pelo `UserSeeder` com senha padrão `password`:

| Nome | E-mail | Role | Permissões |
|---|---|---|---|
| Administrador | `admin@example.com` | `admin` | Acesso total |
| Operador | `operator@example.com` | `operator` | Entradas, saídas, consultas |
| Visualizador | `viewer@example.com` | `viewer` | Somente leitura |

> O seeder é idempotente: executar `php artisan db:seed --class=UserSeeder` novamente não duplica os usuários. Altere as senhas antes de qualquer deploy em produção.

## Rodar apenas o seeder de usuários

```bash
php artisan db:seed --class=UserSeeder
```
