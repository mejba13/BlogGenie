#!/bin/bash

# Navigate to the project directory
cd /var/www/html

# Install Composer dependencies
sudo composer install --no-dev --ignore-platform-reqs

# Run npm install and build (if required)
sudo npm install --prefix /var/www/html
sudo npm run build --prefix /var/www/html

# Optimize the application (optional but recommended for production)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set appropriate permissions for the project directory
sudo chown -R ec2-user:ec2-user /var/www/html

# Output message indicating deployment success
echo "Application deployed successfully."
