#!/bin/sh

composer install --optimize-autoloader

until pg_isready -h db -U ${DB_USER} -d ${DB_NAME}; do
    echo "Waiting for PostgreSQL to be ready..."
    sleep 2
done

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

exec php-fpm