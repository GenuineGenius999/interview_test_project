FROM php:8.1-fpm-bullseye

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/tmp/composer

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install --no-install-recommends -y git libzip-dev unzip \
    && docker-php-ext-install bcmath pdo_mysql zip \
    && groupmod --gid "${GID}" www-data \
    && usermod --uid "${UID}" --gid "${GID}" www-data \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/local.ini
COPY .docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
