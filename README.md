# Multi-Tenant Ticket Desk

A small, production-shaped Laravel 10 ticket management application for technical interviews. Organizations share one MySQL database, while every tenant-owned query and authorization decision explicitly restricts data by `organization_id`.

## Architecture

- Laravel 10 and PHP 8.1 with server-rendered Blade views.
- Explicit row-level tenancy with policies, Form Requests, and tenant-scoped ticket resolution.
- Nginx and PHP-FPM in Docker.
- MySQL 5.7.31 with separate development and testing databases.
- Node 18, Laravel Mix, and the existing Vue 2-compatible asset pipeline.
- Bind-mounted source code with named volumes for MySQL data, Composer dependencies, and npm dependencies.

The application intentionally has no public registration, password reset, API, queue, cache server, or third-party tenancy package.

## Prerequisites

- Docker Desktop or Docker Engine with the Compose plugin.
- Ports `8080` and `3307` available, or alternative values configured in a root `.env` file.

## Initial setup

Run these steps from the repository root.

1. Start Docker Desktop, or start the Docker Engine on Linux. Wait until Docker reports that the engine is running. You can confirm it is available with:

```bash
docker info
```

2. Build the application images and start MySQL first:

```bash
docker compose build
docker compose up -d mysql
```

3. Install locked PHP and JavaScript dependencies into their named volumes:

```bash
docker compose run --rm php composer install --no-interaction --prefer-dist
docker compose run --rm npm npm ci
```

4. Start the full environment:

```bash
docker compose up -d
```

5. Initialize deterministic development data:

```bash
docker compose exec -T php sh -lc 'test "$APP_ENV" = local && test "$DB_DATABASE" = interview_test && php artisan migrate:fresh --seed --no-interaction'
```

6. Confirm MySQL and PHP show as healthy and Nginx is running:

```bash
docker compose ps
```

Open [http://localhost:8080](http://localhost:8080) and log in with one of the development accounts listed below.

## Start after initial setup

For normal daily use, start Docker Desktop or Docker Engine, open a terminal in the repository root, and run:

```bash
docker compose up -d
docker compose ps
```

The named volumes preserve the database and installed dependencies between starts. You do not need to rerun Composer, npm, migrations, or seeds unless dependencies or database setup changed.

## Daily workflow

```bash
# Start containers
docker compose up -d

# Stop containers without deleting named volumes
docker compose down

# Refresh PHP dependencies from composer.lock
docker compose run --rm php composer install --no-interaction --prefer-dist

# Completely refresh the named node_modules volume from package-lock.json
docker compose run --rm npm npm ci

# Inspect application and web-server logs
docker compose logs --tail=100 php nginx
tail -n 100 storage/logs/laravel.log
```

## Reset development data safely

This command checks both the Laravel environment and database name before resetting. It does not delete Docker volumes.

```bash
docker compose exec -T php sh -lc 'test "$APP_ENV" = local && test "$DB_DATABASE" = interview_test && php artisan migrate:fresh --seed --no-interaction'
```

## Tests

Tests force `APP_ENV=testing` and `DB_DATABASE=interview_test_testing` before Laravel boots. This prevents `RefreshDatabase` from operating on the development database.

Fresh MySQL volumes create the testing database automatically. For a named volume created before the testing database was added, run this one-time command:

```bash
docker compose exec -T mysql sh -lc 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -e "CREATE DATABASE IF NOT EXISTS interview_test_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON interview_test_testing.* TO '\''interview_test'\''@'\''%'\''; FLUSH PRIVILEGES;"'
```

Run the PHPUnit feature suite:

```bash
docker compose run --rm php ./vendor/bin/phpunit
```

Build front-end assets once:

```bash
docker compose run --rm npm npm run production
```

## Development accounts

All seeded accounts use the local-development password `password`.

| Organization | Role | Email |
| --- | --- | --- |
| Northwind Services | Manager | `manager@northwind.test` |
| Northwind Services | Member | `member@northwind.test` |
| Contoso Operations | Manager | `manager@contoso.test` |
| Contoso Operations | Member | `member@contoso.test` |

## Row-level tenancy

Each user belongs to exactly one organization. Ticket list queries begin with the signed-in user's `organization_id`, and search clauses are grouped inside that tenant restriction. Ticket detail and status routes resolve records through tenant-scoped middleware, so a cross-tenant identifier consistently returns `404`. A `TicketPolicy` separately authorizes viewing, creation, and manager-only status changes. Browser input never controls `organization_id` or `created_by`.

## Documentation

- [Documentation index](docs/README.md)
- [Development environment](docs/development.md)
- [Tenancy and authorization](docs/tenancy.md)
