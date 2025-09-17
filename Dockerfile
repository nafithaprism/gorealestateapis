# syntax=docker/dockerfile:1

#############################################
# Stage 1: Composer deps (vendor)
#############################################
FROM composer:2 AS vendor
WORKDIR /app

# Composer hygiene + consistent platform
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PLATFORM_PHP=8.2.0

# Install dependencies using only composer files for better caching
COPY composer.json composer.lock* ./

# Install strictly; if a platform/ext mismatch happens, retry ignoring platform reqs
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

# Copy the full application now
COPY . .

# Optimize autoload
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# HARD GUARD: fail build if Bref isn't actually installed
RUN php -r "require 'vendor/autoload.php'; if (!class_exists('Bref\\FpmRuntime\\Main')) {fwrite(STDERR, \"ERROR: Bref is not installed. Did you run: composer require 'bref/bref:^2.0' and commit composer.lock?\\n\"); exit(1);} echo \"Bref present\\n\";"


#############################################
# Stage 2: (Optional) Build front-end assets
#############################################
FROM node:20-alpine AS assets
WORKDIR /app

# Bring the whole tree from vendor stage so we can conditionally build if package.json exists
COPY --from=vendor /app /app

# Ensure public dir exists so the final COPY always has a source
RUN mkdir -p public

# If there is a package.json, install and build (Vite/Laravel Mix/etc.)
RUN if [ -f package.json ]; then \
      npm ci && npm run build; \
    else \
      echo "No package.json found; skipping front-end build"; \
    fi


#############################################
# Stage 3: Prepare Laravel (artisan, caches)
#############################################
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# Bring in the application (including vendor)
COPY --from=vendor /app /var/task

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_STORAGE=/tmp \
    LOG_CHANNEL=stderr

# Ensure cache dir/files exist
RUN mkdir -p bootstrap/cache \
 && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Best-effort package discovery (won't fail build)
RUN php artisan package:discover --ansi || true
# Optional (enable only if safe for your app):
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true
# RUN php artisan view:cache    || true


#############################################
# Stage 4: Final Lambda Runtime (Bref FPM)
#############################################
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy the prepared PHP app
COPY --from=build /var/task /var/task

# Copy built front-end assets (if any). This works even when assets were skipped.
COPY --from=assets /app/public /var/task/public

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_STORAGE=/tmp \
    LOG_CHANNEL=stderr \
    BREF_HANDLER=public/index.php

# Ensure cache dir exists (harmless if already there)
RUN mkdir -p bootstrap/cache

# Lambda will use Bref runtime and call the handler
CMD ["public/index.php"]





# # ================================
# # Stage 1: Composer (build vendor)
# # ================================
# FROM composer:2 AS vendor
# WORKDIR /app

# # Composer hygiene
# ENV COMPOSER_ALLOW_SUPERUSER=1 \
#     COMPOSER_MEMORY_LIMIT=-1 \
#     COMPOSER_PLATFORM_PHP=8.2.0

# # Copy only composer files first for better caching
# COPY composer.json composer.lock* ./

# # Install (strict), fallback to ignore platform reqs if needed
# RUN composer install \
#       --no-dev \
#       --prefer-dist \
#       --no-interaction \
#       --no-progress \
#       --no-scripts \
#       --no-ansi -vvv \
#   || (echo "Composer strict install failed, retrying with --ignore-platform-reqs" && \
#       composer install \
#         --no-dev \
#         --prefer-dist \
#         --no-interaction \
#         --no-progress \
#         --no-scripts \
#         --no-ansi --ignore-platform-reqs -vvv)

# RUN php -r "require 'vendor/autoload.php'; exit(class_exists('Bref\\FpmRuntime\\Main')?0:1);"

# # Copy the rest of the app now
# COPY . .

# # IMPORTANT: run again in case composer.lock changed in the full copy (e.g. after adding bref)
# RUN composer install \
#       --no-dev \
#       --prefer-dist \
#       --no-interaction \
#       --no-progress \
#       --no-scripts \
#       --no-ansi -vvv \
#   || true

# # Optimize autoload
# RUN composer dump-autoload -o --classmap-authoritative --no-scripts


# # ================================================
# # Stage 3: Prepare Laravel app (cache / bootstrap)
# # ================================================
# FROM bref/php-82-fpm:2 AS build
# WORKDIR /var/task

# # Bring in app (including vendor/)
# COPY --from=vendor /app /var/task


# ENV APP_ENV=production \
#     APP_DEBUG=false \
#     APP_STORAGE=/tmp

# # Make sure Laravel cache dir exists and is writable
# RUN mkdir -p bootstrap/cache \
#  && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
#  && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# # Best effort: discover packages (don’t fail build if something needs DB)
# RUN php artisan package:discover --ansi || true
# # Optional (faster cold starts if safe for your app):
# # RUN php artisan config:cache  || true
# # RUN php artisan route:cache   || true
# # RUN php artisan view:cache    || true


# # ===============================================
# # Stage 4: Final runtime for AWS Lambda (Bref FPM)
# # ===============================================
# FROM bref/php-82-fpm:2 AS production
# WORKDIR /var/task

# # Copy fully prepared app
# COPY --from=build /var/task /var/task

# # Ensure cache dir exists
# RUN mkdir -p bootstrap/cache

# # (Optional) make intent explicit; Bref will look for this front controller
# ENV BREF_HANDLER=public/index.php

# # Lambda starts the runtime, which loads public/index.php via Bref's FPM
# CMD ["public/index.php"]
