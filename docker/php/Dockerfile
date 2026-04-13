FROM php:8.3-fpm-alpine

# Minimal system packages placeholder for future production hardening.
RUN apk add --no-cache bash git unzip sqlite

# Minimal PHP extensions for Laravel with SQLite support.
RUN docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www

# Composer is copied from the official image to keep this template clean.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Source code is mounted via volume in docker-compose for local development.
CMD ["php-fpm"]

