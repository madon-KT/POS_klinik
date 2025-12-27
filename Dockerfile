FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zip \
    unzip

# PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# PHP-FPM socket config
RUN mkdir -p /var/run && chown -R www-data:www-data /var/run \
 && sed -i 's|listen = .*|listen = /var/run/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.owner = nobody|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.group = nobody|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.mode = 0660|listen.mode = 0660|' /usr/local/etc/php-fpm.d/www.conf

# Nginx & Supervisor config
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# HTTP port
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
