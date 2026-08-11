# Development environment

## Services

The Compose environment contains four services:

| Service | Responsibility |
| --- | --- |
| `nginx` | Serves public assets and forwards PHP requests to PHP-FPM. |
| `php` | Runs PHP-FPM, Composer, and Laravel Artisan commands. |
| `npm` | Installs JavaScript dependencies and watches Vue 2 assets. |
| `mysql` | Provides the MySQL 5.7.31 development database. |

The database version follows the established `cme-customer-portal` development convention.

## Storage

The source directory is bind-mounted into the application containers so code can be edited on the host. Stateful or generated dependencies are isolated in named Docker volumes:

- `mysql_data` stores database files.
- `composer_vendor` stores PHP dependencies.
- `npm_node_modules` stores JavaScript dependencies.

`docker compose down` preserves these volumes. Use `docker compose down --volumes` only when intentionally resetting the development environment.

## Configuration

Defaults are suitable for local development. Override them in a root `.env` file when needed:

| Variable | Default |
| --- | --- |
| `APP_PORT` | `8080` |
| `DB_FORWARD_PORT` | `3307` |
| `DB_DATABASE` | `interview_test` |
| `DB_USERNAME` | `interview_test` |
| `DB_PASSWORD` | `interview_test` |
| `DB_ROOT_PASSWORD` | `interview_test_root` |
| `DOCKER_UID` | `1000` |
| `DOCKER_GID` | `1000` |

The committed credentials are development-only defaults. Do not reuse them in a deployed environment.

## Common commands

```bash
# Start or rebuild the environment
docker compose up --build

# Run the test suite
docker compose exec php php artisan test

# Run database migrations
docker compose exec php php artisan migrate

# Build front-end assets once
docker compose run --rm npm npm run development
```
