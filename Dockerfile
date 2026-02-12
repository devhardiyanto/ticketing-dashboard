FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    nodejs \
    npm \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first to leverage cache
COPY composer.json composer.lock ./

# Install ALL dependencies (including dev) for build
RUN composer install --no-interaction --no-scripts --prefer-dist

# Copy package files for frontend build
COPY package.json package-lock.json* ./

# Install node dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Setup Laravel environment for build (required for wayfinder plugin)
RUN cp .env .env && php artisan key:generate

# Build frontend assets
RUN npm run build

# Remove dev dependencies after build and regenerate autoloader
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist && \
    composer dump-autoload --optimize --no-dev

# Remove node_modules to keep image smaller
RUN rm -rf node_modules

# Clear bootstrap cache (will be rebuilt at runtime with correct env)
RUN rm -rf bootstrap/cache/*.php

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Expose HTTP port
EXPOSE 8080

# Start both PHP-FPM and Nginx
CMD ["/start.sh"]
