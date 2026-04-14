#!/bin/sh
set -e

echo "==> Running Laravel bootstrap..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding database..."
php artisan db:seed --force

echo "==> Backfilling incident vehicle photos..."
php artisan tvrs:backfill-vehicle-photos

echo "==> Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
