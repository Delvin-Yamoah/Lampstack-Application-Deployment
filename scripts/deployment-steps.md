# LAMP Stack Deployment Steps

## Server-Side Configuration

1. **Copy the health check file to your server**
   ```bash
   sudo cp healthcheck.php /var/www/html/
   ```

2. **Optimize Apache for better performance**
   ```bash
   sudo cp apache-optimization.conf /etc/apache2/mods-available/mpm_prefork.conf
   sudo systemctl restart apache2
   ```

3. **Enable Apache modules for performance**
   ```bash
   sudo a2enmod expires headers deflate
   sudo systemctl restart apache2
   ```

4. **Set up application update script**
   ```bash
   sudo cp update_app.sh /home/ubuntu/
   sudo chmod +x /home/ubuntu/update_app.sh
   ```

## AWS Management Console Steps

1. **Create an AMI from your EC2 instance**
   - EC2 console → Select instance → Actions → Image and templates → Create image
   - Name: "LAMP-Stack-AMI"
   - Description: "LAMP Stack with configured Apache and PHP"

2. **Set up a Launch Template**
   - EC2 → Launch Templates → Create launch template
   - Name: "LAMP-Stack-Template"
   - AMI: Select your newly created AMI
   - Instance type: t2.micro (or appropriate size)
   - Key pair: Select your existing key pair
   - Security groups: Select your existing security group
   - Advanced details → User data: Copy content from user-data.sh

3. **Create an Auto Scaling Group**
   - EC2 → Auto Scaling Groups → Create Auto Scaling group
   - Name: "LAMP-Stack-ASG"
   - Launch template: Select your template
   - VPC and subnets: Select at least two subnets in different AZs
   - Configure group size:
     - Desired: 2
     - Min: 1
     - Max: 4
   - Scaling policies: Target tracking
     - Metric: Average CPU utilization
     - Target value: 70%

4. **Set up Application Load Balancer**
   - EC2 → Load Balancers → Create load balancer → Application Load Balancer
   - Name: "LAMP-Stack-ALB"
   - Scheme: Internet-facing
   - Listeners: HTTP (port 80)
   - AZs: Select the same subnets as your Auto Scaling Group
   - Security groups: Create new or use existing (allow HTTP port 80)
   - Target group:
     - Name: "LAMP-Stack-TG"
     - Protocol: HTTP
     - Port: 80
     - Health check path: /healthcheck.php
   - Register targets: Select your Auto Scaling Group

5. **Update RDS for High Availability**
   - RDS → Databases → Select your database
   - Modify
   - Enable Multi-AZ deployment
   - Apply immediately

6. **Set up CloudWatch Alarms**
   - CloudWatch → Alarms → Create alarm
   - Select metric: EC2 → Per-Instance Metrics → CPUUtilization
   - Conditions: Greater than 80% for 5 minutes
   - Configure actions: Create an SNS topic and add your email
   - Name: "High-CPU-Alarm"