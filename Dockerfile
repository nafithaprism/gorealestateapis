# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Composer hygiene
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PLATFORM_PHP=8.2.0

# Copy only composer files first for better caching
COPY composer.json composer.lock* ./

# Install (strict), fallback to ignore platform reqs if needed
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

# Copy the rest of the app now
COPY . .

# IMPORTANT: run again in case composer.lock changed in the full copy (e.g. after adding bref)
RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --no-ansi -vvv \
  || true

# Optimize autoload
RUN composer dump-autoload -o --classmap-authoritative --no-scripts


# ==================================================
# Stage 2: Build frontend assets with Vite (optional)
# ==================================================
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi
COPY resources resources
# If you use a .env for Vite, copy it (optional)
# COPY .env .env
RUN if [ -f package.json ]; then npm run build; fi


# ================================================
# Stage 3: Prepare Laravel app (cache / bootstrap)
# ================================================
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# Bring in app (including vendor/)
COPY --from=vendor /app /var/task

# Bring in built assets if present
# (Vite typically outputs to public/build)
COPY --from=assets /app/public /var/task/public

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_STORAGE=/tmp

# Make sure Laravel cache dir exists and is writable
RUN mkdir -p bootstrap/cache \
 && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Best effort: discover packages (don’t fail build if something needs DB)
RUN php artisan package:discover --ansi || true
# Optional (faster cold starts if safe for your app):
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true
# RUN php artisan view:cache    || true


# ===============================================
# Stage 4: Final runtime for AWS Lambda (Bref FPM)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy fully prepared app
COPY --from=build /var/task /var/task

# Ensure cache dir exists
RUN mkdir -p bootstrap/cache

# (Optional) make intent explicit; Bref will look for this front controller
ENV BREF_HANDLER=public/index.php

# Lambda starts the runtime, which loads public/index.php via Bref's FPM
CMD ["public/index.php"]
