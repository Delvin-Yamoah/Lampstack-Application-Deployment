#!/bin/bash
# Script to deploy the PHP application to EC2 instance

# Exit on error
set -e

echo "===== Deploying PHP Application ====="

# Check if target directory is provided
if [ -z "$1" ]; then
  TARGET_DIR="/var/www/html"
else
  TARGET_DIR="$1"
fi

# Copy PHP files to target directory
echo "Copying PHP files to $TARGET_DIR..."
sudo cp index.php users.php add_user.php setup_database.php config.php "$TARGET_DIR/"

# Create info.php file
echo "Creating info.php file..."
echo "<?php phpinfo(); ?>" | sudo tee "$TARGET_DIR/info.php" > /dev/null

# Set proper permissions
echo "Setting proper permissions..."
sudo chown -R www-data:www-data "$TARGET_DIR"
sudo chmod -R 755 "$TARGET_DIR"

# Set database connection environment variables
echo "Setting up environment variables..."
echo "export DB_HOST='your-rds-endpoint.rds.amazonaws.com'" | sudo tee -a /etc/apache2/envvars
echo "export DB_USER='admin'" | sudo tee -a /etc/apache2/envvars
echo "export DB_PASS='your-password'" | sudo tee -a /etc/apache2/envvars
echo "export DB_NAME='myapp'" | sudo tee -a /etc/apache2/envvars

# Restart Apache to apply changes
echo "Restarting Apache..."
sudo systemctl restart apache2

echo "===== Deployment Complete ====="
echo "Access your application at: http://your-ec2-public-ip/"
echo "Remember to update the database connection parameters in config.php or environment variables"