#!/bin/bash

# Copy system parameter
sudo touch /var/www/html/.env

# Run Composer install
sudo composer install --no-dev --ignore-platform-reqs -d /var/www/html

# Install Laravel Telescope (if required)
cd /var/www/html
if grep -q TelescopeServiceProvider "config/app.php"; then
  echo "Telescope is already installed."
else
  composer require laravel/telescope --dev
  php artisan telescope:install
  php artisan migrate
fi

# Run npm build
sudo npm install --prefix /var/www/html
sudo npm run build --prefix /var/www/html

# Set correct user permissions
sudo chown -R ec2-user:apache /var/www/html

# Optimize and clear caches
sudo php /var/www/html/artisan optimize:clear

# Publish Horizon (if needed)
sudo php /var/www/html/artisan horizon:publish

# Install Supervisor and restart Horizon
sudo yum install -y supervisor
sudo systemctl enable supervisord
sudo systemctl start supervisord
sudo supervisorctl update
sudo supervisorctl restart horizon

# Cache config, routes, and views
sudo php /var/www/html/artisan config:cache
sudo php /var/www/html/artisan route:cache
sudo php /var/www/html/artisan view:cache
