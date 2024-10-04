#!/bin/bash
# Install PHP and other dependencies
sudo yum -y update
sudo yum -y install httpd
sudo yum -y install php php-mbstring php-xml php-bcmath php-zip
sudo yum -y install composer

# Navigate to the Laravel project directory
cd /var/www/html

# Install PHP dependencies using Composer
composer install --no-dev --prefer-dist --optimize-autoloader
