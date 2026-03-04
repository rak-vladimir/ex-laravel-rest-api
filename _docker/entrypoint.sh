#!/bin/sh
set -e

if [ -d "storage" ]; then
  chmod -R 775 storage bootstrap/cache || true
fi

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  php artisan migrate --force
fi

exec php-fpm
