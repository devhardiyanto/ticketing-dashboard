#!/bin/sh

# Clear cached config (built with .env.example values)
php artisan key:generate

# Clear cached config (built with .env.example values)
php artisan config:clear

# Rebuild cache with runtime environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations automatically
php artisan migrate --force

# Fix runtime permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
