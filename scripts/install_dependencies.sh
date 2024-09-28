#!/bin/bash
cd /var/www/html
composer install --no-dev --prefer-dist --optimize-autoloader
