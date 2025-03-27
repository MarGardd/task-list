#!/bin/sh

composer install --optimize-autoloader

until pg_isready -h db -U ${DB_USER} -d ${DB_NAME}; do
    echo "Waiting for PostgreSQL to be ready..."
    sleep 2
done

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console lexik:jwt:generate-keypair --skip-if-exists

php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:update --force --env=test

exec php-fpm