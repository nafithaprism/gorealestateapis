# ================================
# Stage 1: Build vendor on PHP 8.2
# ================================
FROM php:8.2-cli-alpine AS vendor
WORKDIR /app

# Use the Composer binary from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

# Cache-friendly: copy only composer files first
COPY composer.json composer.lock* ./

# First try a strict install; if platform/ext checks bite, retry ignoring them
RUN set -eux; \
    composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts -vvv \
  || (echo "Composer strict install failed, retrying with --ignore-platform-reqs" && \
      composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --ignore-platform-reqs -vvv)

# Copy the full app and optimize autoload
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# ================================================
# Stage 2: (optional) warm up package discovery
# ================================================
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# Bring in the app (including /vendor from previous stage)
COPY --from=vendor /app /var/task

# Ensure the cache dir exists (runtime will write to /tmp, but presence helps)
RUN mkdir -p bootstrap/cache

# Best effort: discover packages; don't fail image if something needs env/db
RUN php artisan package:discover --ansi || true
# Optionally:
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true

# ===============================================
# Stage 3: Runtime for AWS Lambda (Bref PHP 8.2)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

COPY --from=build /var/task /var/task

# Runtime defaults for serverless (you can also set these in Lambda console)
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    APP_STORAGE=/tmp \
    VIEW_COMPILED_PATH=/tmp/storage/framework/views \
    APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php \
    APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php \
    APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php \
    APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php \
    SESSION_DRIVER=array \
    CACHE_DRIVER=array \
    QUEUE_CONNECTION=sync

# Ensure writable dirs exist at cold start
RUN mkdir -p /tmp/bootstrap/cache /tmp/storage/framework/views

# Bref FPM handler points to Laravel's front controller
ENV BREF_HANDLER=public/index.php
CMD ["public/index.php"]





# # ============================
# # Stage 1: Build vendors (PHP 8.2)
# # ============================
# FROM php:8.2-cli-alpine AS vendor
# WORKDIR /app

# # System deps needed for Composer + zip archives
# RUN apk add --no-cache git unzip libzip-dev \
#     && docker-php-ext-install zip

# # Install Composer (from official image)
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1

# # Install PHP deps based on your lock file (no scripts, no dev)
# COPY composer.json composer.lock* ./
# RUN composer install \
#       --no-dev \
#       --prefer-dist \
#       --no-interaction \
#       --no-progress \
#       --no-scripts

# # Bring in the rest of the app
# COPY . .

# # Optimize autoloaders; sanity-check Bref class exists
# RUN composer dump-autoload -o --no-scripts \
#  && php -r "require 'vendor/autoload.php'; exit(class_exists('Bref\\FpmRuntime\\Main')?0:1);"

# # ===========================================
# # Stage 2: Warm Laravel caches using Bref 8.2
# # ===========================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task

# # Copy prepared app (including /vendor)
# COPY --from=vendor /app /var/task

# # Ensure cache dir and manifest files exist (avoid runtime writes)
# RUN mkdir -p bootstrap/cache \
#  && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
#  && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");' \
#  && php artisan package:discover --ansi || true
# # If safe for your app, you can also cache config/routes/views:
# # RUN php artisan config:cache  || true
# # RUN php artisan route:cache   || true
# # RUN php artisan view:cache    || true

# # ==========================================
# # Stage 3: Final runtime for AWS Lambda FPM
# # ==========================================
# FROM bref/php-82-fpm:2
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # Keep Lambda filesystem read-only friendly
# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     LOG_CHANNEL=stderr
# # (If you rely on storage/, point logs/sessions/cache to external services
# # or use the Laravel Bridge to redirect storage to /tmp.)

# # Bref FPM handler: Laravel front controller
# CMD ["public/index.php"]
