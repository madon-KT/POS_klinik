# =================================================
# Stage 1: Frontend Build (Vite - Node 20)
# =================================================
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy dependency files
COPY package.json package-lock.json ./
RUN npm install

# Copy frontend source & config
COPY resources ./resources
COPY vite.config.js .
COPY postcss.config.js .
COPY tailwind.config.js .

# Build frontend assets
RUN npm run build


# =================================================
# Stage 2: Backend (Laravel)
# =================================================
FROM php:8.2-fpm

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel application
COPY . .

# Copy compiled Vite assets
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# Run Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
