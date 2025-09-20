#!/usr/bin/env bash
set -e

echo "🚀 Starting Laravel container..."

# Wait for DB (if DB_HOST is set)
if [ -n "${DB_HOST}" ]; then
  echo "⏳ Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
  for i in {1..30}; do
    (echo > /dev/tcp/${DB_HOST}/${DB_PORT:-3306}) >/dev/null 2>&1 && break
    sleep 2
  done
fi

# Cache configs/routes/views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Storage symlink
php artisan storage:link || true

# Run migrations on first boot if enabled
if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
  php artisan migrate --force || true
fi

exec "$@"
