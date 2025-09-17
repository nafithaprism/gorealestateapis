# ================================
# Stage 1: Composer (build vendor)
# ================================
FROM composer:2 AS vendor
WORKDIR /app

# Composer hygiene + match Lambda PHP
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PLATFORM_PHP=8.2.0

# Install deps with good layer cache
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

# Fail early if bref/bref is not present
RUN php -r "require 'vendor/autoload.php'; if (!class_exists('Bref\\FpmRuntime\\Main')) {fwrite(STDERR, \"ERROR: bref/bref not installed. Run 'composer require bref/bref:^2'.\\n\"); exit(1);} echo \"Bref found.\\n\";"

# Bring in the app
COPY . .

# Ensure autoloads are optimized (no-op if lock unchanged)
RUN composer install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --no-ansi -vvv || true
RUN composer dump-autoload -o --classmap-authoritative --no-scripts




# ================================================
# Stage 3: Prepare Laravel app (cache / bootstrap)
# ================================================
FROM bref/php-82-fpm:2 AS build
WORKDIR /var/task

# App + vendor from composer stage
COPY --from=vendor /app /var/task

# Bring in built assets (public/build, etc.)
COPY --from=assets /app/public /var/task/public

# Production-ish env for build-time artisan
ENV APP_ENV=production \
    APP_DEBUG=false

# Ensure cache dir/files exist to prevent runtime writes under /var/task
RUN mkdir -p bootstrap/cache \
 && php -r 'file_exists("bootstrap/cache/packages.php") || file_put_contents("bootstrap/cache/packages.php","<?php return [];");' \
 && php -r 'file_exists("bootstrap/cache/services.php") || file_put_contents("bootstrap/cache/services.php","<?php return [];");'

# Discover providers (best effort)
RUN php artisan package:discover --ansi || true
# If safe for your app, uncomment for faster cold starts:
# RUN php artisan config:cache  || true
# RUN php artisan route:cache   || true
# (Avoid view:cache here; we compile views to /tmp at runtime)


# ===============================================
# Stage 4: Final runtime for AWS Lambda (Bref FPM)
# ===============================================
FROM bref/php-82-fpm:2 AS production
WORKDIR /var/task

# Copy fully prepared app
COPY --from=build /var/task /var/task

# Redirect writable paths to /tmp (Lambda's writable filesystem)
RUN mkdir -p /tmp/bootstrap/cache /tmp/views \
 && rm -rf bootstrap/cache \
 && ln -s /tmp/bootstrap/cache bootstrap/cache

# Laravel runtime settings for Lambda
ENV VIEW_COMPILED_PATH=/tmp/views \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=cookie \
    CACHE_STORE=array \
    BREF_HANDLER=public/index.php

# Start Bref FPM runtime → serves Laravel via API Gateway/ALB event
CMD ["public/index.php"]
