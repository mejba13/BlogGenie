#!/bin/bash

# Navigate to the application directory
cd /var/www/html

# Install Composer dependencies
sudo composer install --no-dev --prefer-dist --optimize-autoloader

# Set file permissions
sudo chown -R ec2-user:ec2-user /var/www/html
sudo chmod -R 755 /var/www/html/storage
sudo chmod -R 755 /var/www/html/bootstrap/cache

# Install npm dependencies (for front-end assets if applicable)
sudo npm install
sudo npm run build

# Generate the application key (if not already done)
php artisan key:generate

# Clear and cache configuration, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if needed)
php artisan migrate --force
