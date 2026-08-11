# Development environment

## Services

| Service | Responsibility |
| --- | --- |
| `nginx` | Serves public assets and forwards PHP requests to PHP-FPM. |
| `php` | Runs PHP-FPM, Composer, PHPUnit, and Laravel Artisan commands. |
| `npm` | Watches and rebuilds front-end assets under Node 18. |
| `mysql` | Provides MySQL 5.7.31 development and testing databases. |

The database version follows the established `cme-customer-portal` convention. MySQL remains on port `3306` inside the Docker network and is exposed on host port `3307` by default.

## Storage

The host source directory is bind-mounted into the application containers. Generated or stateful dependencies use named volumes:

- `mysql_data` stores both MySQL databases.
- `composer_vendor` stores locked PHP dependencies.
- `npm_node_modules` stores locked JavaScript dependencies.

Normal setup, reset, start, and stop commands preserve these volumes. Dependency installation is explicit rather than running on every container restart.

## Databases

- `interview_test` contains local development data.
- `interview_test_testing` is exclusively used by PHPUnit.

The test bootstrap forces the testing database configuration before Laravel starts. The development reset command checks `APP_ENV=local` and `DB_DATABASE=interview_test` before running `migrate:fresh --seed`.

## Configuration defaults

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

These are local-development defaults and must not be reused for deployment.

See the root [README](../README.md) for verified installation, reset, test, asset-build, and log commands.
