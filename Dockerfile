FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first to leverage cache
COPY composer.json composer.lock ./

# Install project dependencies (no scripts yet)
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Copy package files for frontend build
COPY package.json package-lock.json* ./

# Install node dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Build frontend assets
RUN npm run build

# Remove node_modules to keep image smaller (optional, but recommended if not using Node at runtime)
# RUN rm -rf node_modules

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
