FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    nodejs \
    npm \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (INI YANG PENTING)
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install --legacy-peer-deps && npm run build

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Nginx + Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
