FROM php:8.1-fpm-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache postgresql-dev && \
    docker-php-ext-install pdo pdo_pgsql

USER 1000:1000

WORKDIR /var/www/html

EXPOSE 9000

# RUN composer install --no-dev --optimize-autoloader