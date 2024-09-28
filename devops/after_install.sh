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

# Install Supervisor using pip
sudo yum install -y python3-pip
sudo pip3 install supervisor

# Create Supervisor config directory and config file
sudo mkdir -p /etc/supervisor/conf.d
sudo echo_supervisord_conf > /etc/supervisor/supervisord.conf

# Start Supervisor
echo "[include]" >> /etc/supervisor/supervisord.conf
echo "files = /etc/supervisor/conf.d/*.conf" >> /etc/supervisor/supervisord.conf
sudo supervisord

# Create Horizon Supervisor config (optional)
sudo tee /etc/supervisor/conf.d/horizon.conf > /dev/null <<EOL
[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
user=ec2-user
redirect_stderr=true
stdout_logfile=/var/log/horizon.log
EOL

# Restart Supervisor and Horizon
sudo supervisorctl reload
sudo supervisorctl restart horizon
