FROM php:8.3-fpm

RUN apt-get update \
 && apt-get install -y git unzip libzip-dev \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
