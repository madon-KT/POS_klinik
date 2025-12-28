FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor curl \
    libzip-dev oniguruma-dev icu-dev \
    zip unzip nodejs npm \
    mariadb-connector-c-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install --legacy-peer-deps && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

