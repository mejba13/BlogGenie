#!/bin/bash

# copy system parameter
sudo touch /var/www/html/.env

# run composer

sudo composer install --no-dev --ignore-platform-reqs -d /var/www/html


# run npm
sudo npm install --prefix /var/www/html
sudo npm run build --prefix /var/www/html

# user permissions
sudo chown -R ec2-user:ec2-user /var/www/html

# selinux permissions
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html(/.*)?'
sudo semanage fcontext -a -t httpd_log_t '/var/www/html/storage/logs(/.*)?'
sudo semanage fcontext -a -t httpd_cache_t '/var/www/html/bootstrap/cache(/.*)?'
sudo restorecon -R -v /var/www/html

# optimize and clear app
#sudo php /var/www/html/artisan clear-compiled
sudo php /var/www/html/artisan optimize:clear

# publish horizon
sudo php /var/www/html/artisan horizon:publish

# supervisor
sudo supervisorctl update
sudo supervisorctl restart horizon

# cache config, route, etc
#sudo php /var/www/html/artisan config:cache
#sudo php /var/www/html/artisan route:cache
sudo php /var/www/html/artisan route:trans:cache
sudo php /var/www/html/artisan view:cache
