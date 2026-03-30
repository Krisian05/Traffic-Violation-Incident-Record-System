#!/bin/sh
set -e

php artisan optimize:clear

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
