#!/bin/bash
# Update system packages and install dependencies
sudo yum update -y
sudo yum install -y httpd php php-cli php-mbstring php-xml php-zip unzip git

# Allow Composer to run as root
export COMPOSER_ALLOW_SUPERUSER=1

# Change to the project directory
cd /var/www/html

# Ensure there's a composer.json file
if [ -f composer.json ]; then
    composer install --no-dev --optimize-autoloader
else
    echo "Error: composer.json not found in /var/www/html"
    exit 1
fi
