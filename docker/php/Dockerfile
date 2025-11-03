FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
        libpq-dev \
        git \
        unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Composer (минимальный установщик)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Установим зависимости, если нужно
RUN composer install --no-interaction --prefer-dist || true
