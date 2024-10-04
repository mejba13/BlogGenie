#!/bin/bash
# Set the correct ownership for the Apache user
sudo chown -R apache:apache /var/www/html

# Set file and directory permissions
find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;

# Set permissions for storage and cache
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R 775 /var/www/html/bootstrap/cache
