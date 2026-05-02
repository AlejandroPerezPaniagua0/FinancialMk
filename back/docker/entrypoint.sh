#!/usr/bin/env bash
# FinancialMk back · container entrypoint
#
# Idempotent boot:
#   1. ensure APP_KEY exists (generate if missing)
#   2. wait for Postgres to be reachable
#   3. run migrations + seeders (no-op when already applied)
#   4. cache config + routes for production
#   5. exec the CMD (php artisan serve by default)

set -e

cd /var/www/html

# 1 · APP_KEY
if [ -z "${APP_KEY}" ]; then
    echo "[entrypoint] APP_KEY empty, generating one in-memory..."
    export APP_KEY="base64:$(openssl rand -base64 32)"
fi

# 2 · Wait for Postgres
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
    echo "[entrypoint] Waiting for Postgres at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
    until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-financialmk}" >/dev/null 2>&1; do
        sleep 1
    done
    echo "[entrypoint] Postgres is ready."
fi

# 3 · Migrate + seed (--force for non-interactive prod, idempotent)
echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

echo "[entrypoint] Running seeders (idempotent)..."
php artisan db:seed --force --no-interaction || true

# 4 · Caches (only in production)
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[entrypoint] Caching config + routes..."
    php artisan config:cache
    php artisan route:cache
fi

# Storage symlink (for any future public asset)
php artisan storage:link 2>/dev/null || true

echo "[entrypoint] Boot complete. Starting: $*"
exec "$@"
