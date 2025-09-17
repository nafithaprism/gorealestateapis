# =================================
# 1) Build vendor with PHP 8.2
# =================================
FROM php:8.2-cli AS vendor
WORKDIR /app

# Tools + PHP extensions commonly needed by Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip zlib1g-dev libzip-dev \
  && docker-php-ext-install -j"$(nproc)" zip pdo_mysql bcmath pcntl \
  && rm -rf /var/lib/apt/lists/*

# Composer (runs under PHP 8.2 so your lock constraints pass)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

# Install prod deps per your composer.json/lock
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Add the rest of the app and optimize autoload
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# Ensure bootstrap/cache files exist in the image
RUN mkdir -p bootstrap/cache \
 && php -r 'is_file("bootstrap/cache/packages.php")||file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'is_file("bootstrap/cache/services.php")||file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Pre-discover packages so Laravel won’t try to write them later
RUN php artisan package:discover --ansi

# (Optional) Prebuild caches into /var/task/bootstrap/cache (read-only at runtime)
RUN php artisan config:cache || true \
 && php artisan route:cache  || true \
 && php artisan view:cache   || true


# =================================
# 2) Final Lambda runtime (Bref PHP 8.2 FPM)
# =================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy built app
COPY --from=vendor /app /var/task

# Minimal runtime env. DO NOT override cache paths to /tmp here.
ENV BREF_HANDLER=public/index.php \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Let Laravel Bridge create /tmp/storage/... at cold start.
# No CMD override needed: Bref will run PHP-FPM for public/index.php





# # ============================
# # 1) Build vendor with PHP 8.2
# # ============================
# FROM php:8.2-cli AS vendor
# WORKDIR /app

# # Minimal tools & extensions Composer may check for
# RUN apt-get update && apt-get install -y --no-install-recommends \
#       git unzip zlib1g-dev libzip-dev \
#   && docker-php-ext-install -j$(nproc) zip pdo_mysql bcmath pcntl \
#   && rm -rf /var/lib/apt/lists/*

# # Composer (runs under PHP 8.2 so your lockfile constraints pass)
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1

# # Install prod deps based on your composer.json/lock
# COPY composer.json composer.lock* ./
# RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# # Add the rest of the app & optimize autoload
# COPY . .
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# # Make sure Laravel's bootstrap cache files exist in the code tree
# RUN mkdir -p bootstrap/cache \
#  && php -r 'is_file("bootstrap/cache/packages.php")||file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
#  && php -r 'is_file("bootstrap/cache/services.php")||file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# # =========================================
# # 2) (Optional) Warm caches during build
# # =========================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task
# COPY --from=vendor /app /var/task

# # Build-time env (safe, no secrets). We keep caches in /var/task for build,
# # but at runtime we’ll point Laravel to /tmp via env variables.
# ENV APP_ENV=production \
#     APP_DEBUG=false

# RUN php artisan package:discover --ansi || true
# # You *can* cache these; harmless if they fail in CI:
# RUN php artisan config:cache || true \
#  && php artisan route:cache  || true \
#  && php artisan view:cache   || true

# # =========================================
# # 3) Final Lambda runtime (Bref PHP 8.2 FPM)
# # =========================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # Writable dirs (Lambda)
# RUN mkdir -p \
#     /tmp/bootstrap/cache \
#     /tmp/views \
#     /tmp/storage/framework/{cache,sessions,views} \
#     /tmp/storage/logs

# # Tell Laravel to use /tmp for *all* caches & views (writable on Lambda)
# ENV BREF_HANDLER=public/index.php \
#     APP_ENV=production \
#     APP_DEBUG=false \
#     LOG_CHANNEL=stderr \
#     APP_STORAGE=/tmp \
#     VIEW_COMPILED_PATH=/tmp/views \
#     APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php \
#     APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php \
#     APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php \
#     APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php \
#     APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php \
#     CACHE_DRIVER=array \
#     SESSION_DRIVER=array

# # Start Bref FPM (will load public/index.php)
# CMD ["public/index.php"]


# # ============================
# # 1) Build vendor with PHP 8.2
# # ============================
# FROM php:8.2-cli AS vendor
# WORKDIR /app

# # Tools & PHP extensions Composer may check for
# RUN apt-get update && apt-get install -y --no-install-recommends \
#       git unzip zlib1g-dev libzip-dev \
#   && docker-php-ext-install -j$(nproc) zip pdo_mysql bcmath pcntl \
#   && rm -rf /var/lib/apt/lists/*

# # Use Composer but run it under PHP 8.2 (this container)
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1

# # Install prod deps (lock is respected; PHP=8.2 so constraints pass)
# COPY composer.json composer.lock* ./
# RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# # Bring in the app and optimize autoload
# COPY . .
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# # Ensure Laravel bootstrap cache files exist so it won't try to write them at runtime
# RUN mkdir -p bootstrap/cache \
#  && php -r 'is_file("bootstrap/cache/packages.php")||file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
#  && php -r 'is_file("bootstrap/cache/services.php")||file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# # =========================================
# # 2) (Optional) Pre-build caches inside Bref
# # =========================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task
# COPY --from=vendor /app /var/task

# # Minimal env so artisan can run during build (NO real secrets here)
# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     VIEW_COMPILED_PATH=/tmp/views \
#     APP_STORAGE=/tmp

# RUN mkdir -p bootstrap/cache /tmp/views \
#  && php artisan package:discover --ansi || true \
#  && php artisan config:cache || true \
#  && php artisan route:cache  || true \
#  && php artisan view:cache   || true

# # =========================================
# # 3) Final Lambda runtime (Bref PHP 8.2 FPM)
# # =========================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # Writable dirs in Lambda
# RUN mkdir -p /tmp/views \
#              /tmp/storage/framework/{cache,sessions,views} \
#              /tmp/storage/logs

# # Runtime configuration (no secrets)
# ENV BREF_HANDLER=public/index.php \
#     VIEW_COMPILED_PATH=/tmp/views \
#     APP_STORAGE=/tmp \
#     LOG_CHANNEL=stderr \
#     CACHE_DRIVER=array \
#     SESSION_DRIVER=array

# CMD ["public/index.php"]










# # ======================
# # 1) Build vendor (PHP 8.2)
# # ======================
# FROM composer:2 AS vendor
# WORKDIR /app

# # Make Composer behave like PHP 8.2 to satisfy your lockfile
# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1 \
#     COMPOSER_PLATFORM_PHP=8.2.29

# # Install prod deps only (matches your composer.json)
# COPY composer.json composer.lock* ./
# RUN composer install \
#       --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# # Bring in the rest of the app and optimize autoload
# COPY . .
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# # Ensure Laravel won't try to write package/service manifests at runtime
# RUN mkdir -p bootstrap/cache \
#  && php -r 'is_file("bootstrap/cache/packages.php")||file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
#  && php -r 'is_file("bootstrap/cache/services.php")||file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# # =========================================
# # 2) (Optional) Build caches inside bref PHP
# # =========================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task
# COPY --from=vendor /app /var/task

# # Minimal env so artisan can run during build
# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
#     VIEW_COMPILED_PATH=/tmp/views \
#     APP_STORAGE=/tmp

# RUN mkdir -p bootstrap/cache /tmp/views
# # Best-effort: if any of these fail, we still proceed
# RUN php artisan package:discover --ansi || true \
#  && php artisan config:cache       || true \
#  && php artisan route:cache        || true \
#  && php artisan view:cache         || true

# # =========================================
# # 3) Final Lambda runtime (Bref PHP 8.2 FPM)
# # =========================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # Writable dirs for Lambda
# RUN mkdir -p /tmp/views \
#              /tmp/storage/framework/{cache,sessions,views} \
#              /tmp/storage/logs

# # Use baked caches under /var/task; write views to /tmp
# ENV BREF_HANDLER=public/index.php \
#     VIEW_COMPILED_PATH=/tmp/views \
#     APP_STORAGE=/tmp \
#     LOG_CHANNEL=stderr \
#     CACHE_DRIVER=array \
#     SESSION_DRIVER=array \
#     APP_CONFIG_CACHE=/var/task/bootstrap/cache/config.php \
#     APP_PACKAGES_CACHE=/var/task/bootstrap/cache/packages.php \
#     APP_SERVICES_CACHE=/var/task/bootstrap/cache/services.php \
#     APP_EVENTS_CACHE=/var/task/bootstrap/cache/events.php

# CMD ["public/index.php"]











# # ================================
# # Stage 1: install dependencies on PHP 8.2
# # ================================
# FROM php:8.2-cli-alpine AS vendor
# WORKDIR /app

# # tools for composer/zip
# RUN apk add --no-cache git unzip libzip-dev && docker-php-ext-install zip

# # composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

# # install prod deps (matches your composer.json)
# COPY composer.json composer.lock* ./
# RUN set -eux; \
#     composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts -vvv \
#   || (echo "Retry with --ignore-platform-reqs" && \
#       composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --ignore-platform-reqs -vvv)

# # bring in the app code now
# COPY . .

# # optimize autoload
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# # ================================
# # Stage 2: prebuild Laravel caches on Bref runtime
# # ================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task

# # copy app (includes vendor/)
# COPY --from=vendor /app /var/task

# # ensure cache dirs exist
# RUN mkdir -p bootstrap/cache storage/framework/views

# # IMPORTANT: remove any bad stubs that return empty arrays
# RUN rm -f bootstrap/cache/services.php bootstrap/cache/packages.php

# # Pre-discover packages/providers so Laravel won't try to write in /var/task
# # Provide safe inline env so no DB/filesystem writes are required
# RUN APP_ENV=production \
#     APP_DEBUG=false \
#     APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
#     LOG_CHANNEL=stderr \
#     CACHE_DRIVER=array \
#     SESSION_DRIVER=array \
#     php artisan package:discover --ansi

# # (Optionally) you can also warm up these if your app supports it without DB:
# # RUN APP_ENV=production php artisan config:cache  || true
# # RUN APP_ENV=production php artisan route:cache   || true

# # ================================
# # Stage 3: runtime image for Lambda
# # ================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # runtime env (tune as needed)
# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     LOG_CHANNEL=stderr \
#     APP_STORAGE=/tmp \
#     SESSION_DRIVER=array \
#     CACHE_DRIVER=array

# # writable paths for runtime
# RUN mkdir -p /tmp/bootstrap/cache /tmp/storage/framework/views

# # Let Bref FPM boot Laravel's front controller
# ENV BREF_HANDLER=public/index.php
# CMD ["public/index.php"]




# # ================================
# # Stage 1: Build vendor on PHP 8.2
# # ================================
# FROM php:8.2-cli-alpine AS vendor
# WORKDIR /app

# # Use the Composer binary from the official image
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1

# # Cache-friendly: copy only composer files first
# COPY composer.json composer.lock* ./

# # First try a strict install; if platform/ext checks bite, retry ignoring them
# RUN set -eux; \
#     composer install \
#       --no-dev \
#       --prefer-dist \
#       --no-interaction \
#       --no-progress \
#       --no-scripts -vvv \
#   || (echo "Composer strict install failed, retrying with --ignore-platform-reqs" && \
#       composer install \
#         --no-dev \
#         --prefer-dist \
#         --no-interaction \
#         --no-progress \
#         --no-scripts \
#         --ignore-platform-reqs -vvv)

# # Copy the full app and optimize autoload
# COPY . .
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# # ================================================
# # Stage 2: (optional) warm up package discovery
# # ================================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task

# # Bring in the app (including /vendor from previous stage)
# COPY --from=vendor /app /var/task

# # Ensure the cache dir exists (runtime will write to /tmp, but presence helps)
# RUN mkdir -p bootstrap/cache

# # Best effort: discover packages; don't fail image if something needs env/db
# RUN php artisan package:discover --ansi || true
# # Optionally:
# # RUN php artisan config:cache  || true
# # RUN php artisan route:cache   || true

# # ===============================================
# # Stage 3: Runtime for AWS Lambda (Bref PHP 8.2)
# # ===============================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# COPY --from=build /var/task /var/task

# # Runtime defaults for serverless (you can also set these in Lambda console)
# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     LOG_CHANNEL=stderr \
#     APP_STORAGE=/tmp \
#     VIEW_COMPILED_PATH=/tmp/storage/framework/views \
#     APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php \
#     APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php \
#     APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php \
#     APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php \
#     SESSION_DRIVER=array \
#     CACHE_DRIVER=array \
#     QUEUE_CONNECTION=sync

# # Ensure writable dirs exist at cold start
# RUN mkdir -p /tmp/bootstrap/cache /tmp/storage/framework/views

# # Bref FPM handler points to Laravel's front controller
# ENV BREF_HANDLER=public/index.php
# CMD ["public/index.php"]





# # # ============================
# # # Stage 1: Build vendors (PHP 8.2)
# # # ============================
# # FROM php:8.2-cli-alpine AS vendor
# # WORKDIR /app

# # # System deps needed for Composer + zip archives
# # RUN apk add --no-cache git unzip libzip-dev \
# #     && docker-php-ext-install zip

# # # Install Composer (from official image)
# # COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # ENV COMPOSER_ALLOW_SUPERUSER=1 \
# #     COMPOSER_MEMORY_LIMIT=-1

# # # Install PHP deps based on your lock file (no scripts, no dev)
# # COPY composer.json composer.lock* ./
# # RUN composer install \
# #       --no-dev \
# #       --prefer-dist \
# #       --no-interaction \
# #       --no-progress \
# #       --no-scripts

# # # Bring in the rest of the app
# # COPY . .

# # # Optimize autoloaders; sanity-check Bref class exists
# # RUN composer dump-autoload -o --no-scripts \
# #  && php -r "require 'vendor/autoload.php'; exit(class_exists('Bref\\FpmRuntime\\Main')?0:1);"

# # # ===========================================
# # # Stage 2: Warm Laravel caches using Bref 8.2
# # # ===========================================
# # FROM bref/php-82-fpm:2 AS build
# # WORKDIR /var/task

# # # Copy prepared app (including /vendor)
# # COPY --from=vendor /app /var/task

# # # Ensure cache dir and manifest files exist (avoid runtime writes)
# # RUN mkdir -p bootstrap/cache \
# #  && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
# #  && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");' \
# #  && php artisan package:discover --ansi || true
# # # If safe for your app, you can also cache config/routes/views:
# # # RUN php artisan config:cache  || true
# # # RUN php artisan route:cache   || true
# # # RUN php artisan view:cache    || true

# # # ==========================================
# # # Stage 3: Final runtime for AWS Lambda FPM
# # # ==========================================
# # FROM bref/php-82-fpm:2
# # WORKDIR /var/task

# # COPY --from=build /var/task /var/task

# # # Keep Lambda filesystem read-only friendly
# # ENV APP_ENV=production \
# #     APP_DEBUG=false \
# #     LOG_CHANNEL=stderr
# # # (If you rely on storage/, point logs/sessions/cache to external services
# # # or use the Laravel Bridge to redirect storage to /tmp.)

# # # Bref FPM handler: Laravel front controller
# # CMD ["public/index.php"]
