#!/bin/bash
# User data script for EC2 instances in Auto Scaling Group

# Update system packages
sudo apt update -y
sudo apt upgrade -y

# Install CloudWatch agent for enhanced monitoring
sudo apt install -y amazon-cloudwatch-agent

# Start CloudWatch agent
sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl -a fetch-config -m ec2 -s -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json

# Connect to RDS database
echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Restart Apache
sudo systemctl restart apache2