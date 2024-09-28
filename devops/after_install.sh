#!/bin/bash

# Navigate to the project directory
cd /var/www/html

# Install Composer dependencies
sudo composer install --no-dev --ignore-platform-reqs

# Install Laravel Telescope (if required)
if grep -q TelescopeServiceProvider "config/app.php"; then
  echo "Telescope is already installed."
else
  composer require laravel/telescope --dev
  php artisan telescope:install
  php artisan migrate
fi

# Run npm install and build
sudo npm install --prefix /var/www/html
sudo npm run build --prefix /var/www/html
