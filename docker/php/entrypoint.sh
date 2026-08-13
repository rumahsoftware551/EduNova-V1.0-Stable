#!/bin/sh
set -eu

mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache/data \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Package discovery is safe to repeat and ensures Sanctum is registered.
php artisan package:discover --ansi >/dev/null 2>&1 || true

# Production config cache. If the environment is still being prepared,
# failure does not prevent maintenance commands such as migrate/key checks.
if [ "${APP_ENV:-production}" = "production" ] && [ -n "${APP_KEY:-}" ]; then
  php artisan config:cache >/dev/null 2>&1 || true
  php artisan view:cache >/dev/null 2>&1 || true
fi

exec "$@"
