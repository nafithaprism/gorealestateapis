# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Install PHP dependencies (no dev, no scripts)
COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# Copy the rest of the application
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts


# ================================================
# Stage 2: Build Laravel caches & manifests (CLI)
# ================================================
FROM bref/php-82-fpm:2 AS cache
WORKDIR /var/task

# Copy app from vendor stage
COPY --from=vendor /app /var/task

# Set prod env so artisan doesn’t try debug things
ENV APP_ENV=production
ENV APP_DEBUG=false

# Ensure cache dir exists
RUN mkdir -p bootstrap/cache \
 && php artisan package:discover --ansi || true \
 && test -f bootstrap/cache/packages.php || php -r 'file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && test -f bootstrap/cache/services.php || php -r 'file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# (Optional) You can also pre-build config/routes/views caches if your app
# can run artisan without DB connections:
# RUN php artisan config:cache || true
# RUN php artisan route:cache  || true
# RUN php artisan view:cache   || true


# ===============================================
# Stage 3: Runtime for AWS Lambda (Bref PHP 8.2)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy fully prepared app from cache stage
COPY --from=cache /var/task /var/task

# Ensure bootstrap/cache exists (will already contain files from cache stage)
RUN mkdir -p bootstrap/cache

# Bref FPM entry point = Laravel front controller
CMD ["public/index.php"]
