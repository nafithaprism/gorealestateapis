# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Match Lambda PHP, speed + stability
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PLATFORM_PHP=8.2.0

# Install deps with good layer caching
COPY composer.json composer.lock* ./
RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --no-ansi -vvv \
  || (echo "Composer strict install failed, retrying with --ignore-platform-reqs" && \
      composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-ansi --ignore-platform-reqs -vvv)

# Fail early if bref/bref is missing
RUN php -r "require 'vendor/autoload.php'; if (!class_exists('Bref\\FpmRuntime\\Main')) {fwrite(STDERR, \"ERROR: bref/bref not installed. Run 'composer require bref/bref:^2'.\\n\"); exit(1);} echo \"Bref found.\\n\";"

# Bring in the rest of the app
COPY . .

# Re-run install in case composer.json/lock changed in the full copy
RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --no-ansi -vvv || true

# Optimize autoload
RUN composer dump-autoload -o --classmap-authoritative --no-scripts


# ================================================
# Stage 2: Prepare Laravel app (cache/bootstrap)
# ================================================
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# Copy app (incl. vendor/)
COPY --from=vendor /app /var/task

# Minimal prod env for build-time artisan
ENV APP_ENV=production \
    APP_DEBUG=false

# Make sure bootstrap/cache exists + stub manifests to avoid writes
RUN mkdir -p bootstrap/cache \
 && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Discover providers (best effort; ignore DB-dependant stuff)
RUN php artisan package:discover --ansi || true
# (Optional, only if safe)
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true
# (Avoid view:cache in build; we write views to /tmp at runtime)


# ===============================================
# Stage 3: Final runtime for AWS Lambda (Bref FPM)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy prepared app
COPY --from=build /var/task /var/task

# Laravel runtime: send all writable caches to /tmp (Lambda-writable)
ENV APP_SERVICES_CACHE=/tmp/services.php \
    APP_PACKAGES_CACHE=/tmp/packages.php \
    APP_CONFIG_CACHE=/tmp/config.php \
    APP_EVENTS_CACHE=/tmp/events.php \
    APP_ROUTES_CACHE=/tmp/routes.php \
    VIEW_COMPILED_PATH=/tmp/views \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=cookie \
    CACHE_STORE=array \
    BREF_HANDLER=public/index.php

# Ensure dirs will exist at cold start (no-op if already)
RUN mkdir -p /tmp /var/task/bootstrap/cache

# Bref starts FPM and serves Laravel via API Gateway/ALB
CMD ["public/index.php"]
