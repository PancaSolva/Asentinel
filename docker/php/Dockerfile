FROM composer:2 AS vendor

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php:8.5-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
        fcgi \
        libzip-dev \
        mysql-client \
        sqlite \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        pdo_mysql \
        pdo_sqlite \
        sqlite3 \
        zip \
    && rm -rf /tmp/* /var/cache/apk/*

COPY --from=vendor /usr/bin/composer /usr/bin/composer
COPY --from=vendor /var/www/vendor /var/www/vendor
COPY --chown=www-data:www-data . .

RUN mkdir -p storage bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R ug+rwx storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php artisan health:check || exit 1

EXPOSE 9000

CMD ["php-fpm"]
