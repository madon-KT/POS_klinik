FROM php:8.2-fpm-alpine

# ===============================
# System dependencies
# ===============================
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zip \
    unzip \
    nodejs \
    npm

# ===============================
# PHP extensions
# ===============================
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    intl

# ===============================
# Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ===============================
# Set working directory
# ===============================
WORKDIR /var/www/html

# ===============================
# Copy application files
# ===============================
COPY . .

# ===============================
# Install PHP dependencies
# ===============================
RUN composer install --no-dev --optimize-autoloader

# ===============================
# Build Vite assets (PRODUCTION)
# ===============================
RUN npm install --legacy-peer-deps && npm run build

# ===============================
# Permissions
# ===============================
RUN chown -R www-data:www-data storage bootstrap/cache

# ===============================
# PHP-FPM socket config
# ===============================
RUN mkdir -p /var/run/php \
 && chown -R www-data:www-data /var/run/php

RUN sed -i 's|listen = .*|listen = /var/run/php/php-fpm.sock|' \
    /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.owner = nobody|listen.owner = www-data|' \
    /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.group = nobody|listen.group = www-data|' \
    /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's|;listen.mode = 0660|listen.mode = 0660|' \
    /usr/local/etc/php-fpm.d/www.conf

# ===============================
# Nginx & Supervisor config
# ===============================
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# ===============================
# Expose HTTP port
# ===============================
EXPOSE 80

# ===============================
# Start services
# ===============================
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
