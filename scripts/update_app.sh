#!/bin/bash
# Script to update application code and restart Apache

cd /var/www/html
git pull origin main
sudo systemctl restart apache2