#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime directories are writable by Apache/PHP
chown -R www-data:www-data storage bootstrap/cache

# Re-create the storage symlink if it isn't present (named volumes may remove it)
if [ ! -L public/storage ]; then
    ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
fi

# Optionally run database migrations on startup
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

exec "$@"
