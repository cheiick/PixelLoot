#!/bin/bash
set -e

if [ "${PUBLIC_STORAGE_DRIVER:-local}" = "local" ]; then
    PUBLIC_STORAGE_PATH="${PUBLIC_STORAGE_PATH:-/var/www/html/storage/app/public}"
    mkdir -p "$PUBLIC_STORAGE_PATH"
    chown -R www-data:www-data "$PUBLIC_STORAGE_PATH"
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public/build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${PUBLIC_STORAGE_DRIVER:-local}" = "local" ]; then
    php artisan storage:link --force
fi

exec "$@"
