#!/bin/bash

# Update the instance
sudo yum -y update

# Install Apache
sudo yum -y install httpd

# Install PHP and necessary extensions
sudo amazon-linux-extras install php8.0
sudo yum -y install php php-mbstring php-xml php-mysqlnd php-gd

# Install Composer (if not installed)
if ! command -v composer &> /dev/null
then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# Install the application dependencies
cd /var/www/html
sudo composer install --no-dev --prefer-dist --optimize-autoloader

# Generate application key (if not already done)
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    php artisan key:generate
fi
