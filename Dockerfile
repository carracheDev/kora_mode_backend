FROM composer:2 AS composer_bin

FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring bcmath opcache \
    && apk del oniguruma-dev libxml2-dev

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080

USER www-data
CMD ["sh", "-c", "php artisan migrate --force || true; php artisan serve --host 0.0.0.0 --port ${PORT}"]
