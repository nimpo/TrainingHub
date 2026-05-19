FROM php:8.3-fpm

RUN apt-get update \
 && apt-get install -y git unzip libzip-dev \
 && docker-php-ext-install zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv/html

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

COPY composer.json composer.lock* ./

RUN groupadd -g 1000 app \
 && useradd -u 1000 -g 1000 -m -s /bin/bash app

RUN git config --system --add safe.directory '*'

COPY git-askpass.sh /usr/local/bin/git-askpass
RUN chmod 0755 /usr/local/bin/git-askpass

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
