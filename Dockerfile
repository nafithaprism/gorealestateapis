# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Make composer behave like PHP 8.2 to match the Lambda runtime
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PLATFORM_PHP=8.2.0

# Install PHP deps first (cache-friendly)
COPY composer.json composer.lock* ./

# Install prod deps only (bridge must be in "require", not require-dev)
RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts

# Copy the rest of the app
COPY . .

# Optimize autoload
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# ================================================
# Stage 2: Prepare Laravel app (optional caching)
# ================================================
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# Bring in app (including vendor/)
COPY --from=vendor /app /var/task

# Best effort: discover packages at build time (doesn't write to /var/task on runtime)
RUN php artisan package:discover --ansi || true
# You can cache config/routes if safe for your app:
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true
# Do NOT pre-create bootstrap/cache/*.php in /var/task (read-only at runtime)

# ===============================================
# Stage 3: Runtime (AWS Lambda – Bref FPM)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy app
COPY --from=build /var/task /var/task

# Runtime env defaults (you can also set these in Lambda console)
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    APP_STORAGE=/tmp \
    VIEW_COMPILED_PATH=/tmp/storage/framework/views \
    APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php \
    APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php \
    APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php \
    APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php

# Ensure directories exist (creation is harmless if they already do)
RUN mkdir -p /tmp/bootstrap/cache /tmp/storage/framework/views

# Explicit handler (Bref FPM points to Laravel front controller)
ENV BREF_HANDLER=public/index.php

# Lambda starts the Bref runtime; this tells it which front controller to serve
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
