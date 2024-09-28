#!/bin/bash
# Update and install necessary dependencies
sudo yum update -y
sudo yum install -y httpd php php-cli php-mbstring php-xml php-zip unzip git

# Install Composer
cd /var/www/html
composer install --no-dev --optimize-autoloader
