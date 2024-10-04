#!/bin/bash
# Enable Apache at startup
sudo systemctl enable httpd

# Restart Apache to load the new deployment
sudo systemctl restart httpd

# Ensure Apache is running
sudo systemctl status httpd
