# ================================
# 1) Build stage: Composer deps
# ================================
FROM php:8.2-fpm-alpine AS build
WORKDIR /var/www

# System deps
RUN apk add --no-cache git unzip libzip-dev oniguruma-dev icu-dev bash \
  && docker-php-ext-install pdo_mysql zip bcmath intl opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

# Copy composer files & install prod deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Copy full app
COPY . .
RUN composer dump-autoload -o --classmap-authoritative --no-scripts

# ================================
# 2) Runtime stage
# ================================
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www

# Install runtime PHP extensions
RUN apk add --no-cache libzip-dev oniguruma icu-data-full bash \
  && docker-php-ext-install pdo_mysql zip bcmath intl opcache \
  && { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=0"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.jit_buffer_size=64M"; \
  } > /usr/local/etc/php/conf.d/opcache.ini

# Copy built app
COPY --from=build /var/www /var/www

# Ensure writable dirs
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Entrypoint will run artisan commands, migrations, etc.
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm", "-F"]
