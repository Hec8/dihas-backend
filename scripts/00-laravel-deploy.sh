#!/usr/bin/env bash
echo "Running composer"
# prestissimo n'est plus nécessaire avec Composer 2.x
composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force

echo "Setting permissions for images directory..."
sudo chown -R www-data:www-data /var/www/html/public/images
sudo chmod -R 755 /var/www/html/public/images

# echo "Running seeds by force..."
# php artisan db:seed --force