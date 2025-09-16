# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Composer hygiene
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Install PHP deps first (faster layer reuse)
COPY composer.json composer.lock* ./

# First attempt: strict install; if it fails (platform/ext mismatch), retry ignoring platform reqs
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

# Copy the rest of the app and finalize autoload
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts


# ==================================================
# Stage 2 (optional): Build frontend assets with Vite
# ==================================================
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN if [ -f package.json ]; then npm ci; fi
COPY resources resources
COPY vite.config.* . 2>/dev/null || true
RUN if [ -f package.json ]; then npm run build; fi


# ================================================
# Stage 3: Build Laravel caches & manifests (CLI)
# ================================================
FROM bref/php-82-fpm:2 AS cache
WORKDIR /var/task

# Copy app from vendor stage (includes /vendor)
COPY --from=vendor /app /var/task

# Set prod env so artisan (if it runs) stays quiet
ENV APP_ENV=production
ENV APP_DEBUG=false

# Ensure cache dir/files exist to avoid runtime boot errors
RUN mkdir -p bootstrap/cache \
 && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Try to discover packages (best effort; will not fail build if a provider needs DB)
RUN php artisan package:discover --ansi || true

# (Optional) If your app can cache config/routes without DB, uncomment for faster cold starts:
# RUN php artisan config:cache || true
# RUN php artisan route:cache  || true
# RUN php artisan view:cache   || true

# Bring in built assets if present
COPY --from=assets /app/public/build /var/task/public/build 2>/dev/null || true


# ===============================================
# Stage 4: Runtime for AWS Lambda (Bref PHP 8.2)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy fully prepared app
COPY --from=cache /var/task /var/task

# Ensure bootstrap/cache exists (already from cache stage, but harmless)
RUN mkdir -p bootstrap/cache

# Bref FPM handler points to Laravel front controller
CMD ["public/index.php"]
