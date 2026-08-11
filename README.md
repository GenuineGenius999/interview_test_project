# Interview Test Project

A deliberately small Laravel 8 and Vue 2 application for conducting technical interviews. The initial application renders a Vue-powered “Hello World” page and provides a Docker-based development environment.

## Stack

- Laravel 8 on PHP 8.1 with PHP-FPM and Composer
- Vue 2.7 built by Laravel Mix on Node 18
- Nginx
- MySQL 5.7.31
- Docker Compose

## Start the project

The application requires Docker with the Compose plugin.

```bash
docker compose up --build
```

Open [http://localhost:8080](http://localhost:8080). The first startup installs PHP and JavaScript dependencies automatically. The Node container continues watching front-end files and rebuilds assets when they change.

Run Laravel commands in the PHP container:

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan test
docker compose exec php composer install
```

Stop the environment while preserving development data and dependencies:

```bash
docker compose down
```

Remove the named development volumes when a completely clean environment is needed:

```bash
docker compose down --volumes
```

## Architecture

Nginx serves the application on port `8080` and forwards PHP requests to PHP-FPM. The source tree is bind-mounted so edits are immediately available in the containers. MySQL data, Composer's `vendor` directory, and npm's `node_modules` directory use named Docker volumes.

Configuration can be overridden through environment variables. See [.env.example](.env.example) for Laravel defaults and [docs/development.md](docs/development.md) for Docker-specific options and common commands.

## Documentation

- [Documentation index](docs/README.md)
- [Development environment](docs/development.md)
