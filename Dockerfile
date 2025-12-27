FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zip \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data storage bootstrap/cache

# FIX PHP-FPM
RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf \
 && mkdir -p /var/run/php \
 && chown -R www-data:www-data /var/run/php \
 && sed -i 's|listen = .*|listen = /var/run/php/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.owner = nobody|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.group = nobody|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.mode = 0660|listen.mode = 0660|' /usr/local/etc/php-fpm.d/www.conf

COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
