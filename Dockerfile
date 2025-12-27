FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor curl \
    libzip-dev oniguruma-dev icu-dev zip unzip

RUN docker-php-ext-install pdo pdo_mysql mbstring zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader \
 && mkdir -p /var/run/php \
 && chown -R www-data:www-data storage bootstrap/cache /var/run/php

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
