# ================================
# 1) Build stage: Composer deps
# ================================
FROM php:8.2-fpm-alpine AS build
WORKDIR /var/www

# Composer
RUN apk add --no-cache git unzip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

# Install PHP extensions needed by Composer to run (zip if your deps need it)
# (No intl compile here)
RUN apk add --no-cache libzip-dev \
 && docker-php-ext-install zip

# Install prod deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Bring in the full app & optimize autoloaders
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# ================================
# 2) Runtime stage: PHP-FPM + extensions
# ================================
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www

# Runtime libs first (no headers)
RUN apk add --no-cache bash icu-libs libzip oniguruma libstdc++

# TEMP build deps for compiling PHP extensions (will be removed)
RUN apk add --no-cache --virtual .build-deps \
      $PHPIZE_DEPS icu-dev libzip-dev oniguruma-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j"$(nproc)" \
      intl pdo_mysql zip bcmath opcache \
 && apk del .build-deps

# Opcache tuning
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=0"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.interned_strings_buffer=8"; \
      echo "opcache.max_accelerated_files=10000"; \
      echo "opcache.validate_timestamps=0"; \
      echo "opcache.jit_buffer_size=64M"; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Copy built app from build stage
COPY --from=build /var/www /var/www

# Permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Entrypoint (artisan caches, migrate, etc.)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm", "-F"]
