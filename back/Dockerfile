# FinancialMk back · PHP 8.2 + Composer + Laravel
#
# Two-stage: vendor install on a builder, then a slim runtime that uses
# the built-in PHP dev server (artisan serve). For higher concurrency
# swap to php-fpm + nginx — see back/README.md.

# ── Stage 1: vendor ───────────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ── Stage 2: runtime ──────────────────────────────────────────────────────
FROM php:8.2-cli-alpine

# System deps + PHP extensions for Postgres, Redis, Intl, BCMath
RUN apk add --no-cache \
        postgresql-client \
        postgresql-dev \
        oniguruma-dev \
        icu-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        bash \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --purge postgresql-dev oniguruma-dev icu-dev libzip-dev

WORKDIR /var/www/html

# App source + vendor from builder
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Permissions for storage / bootstrap cache
RUN chmod -R ug+rwX storage bootstrap/cache

# OPcache tuned for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 8000

# Entrypoint: wait for DB, run migrations + seeders idempotently, serve.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
