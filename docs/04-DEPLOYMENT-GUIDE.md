# Complete Deployment Guide - AWS Management Console Approach

## Why This Deployment Approach

### For Business Leaders
**The Business Case:**
- **Reduced Risk**: GUI-based deployment reduces human error
- **Faster Time to Market**: Visual interfaces speed up configuration
- **Better Governance**: Console actions are logged and auditable
- **Team Accessibility**: Non-command-line experts can participate in deployment
- **Cost Control**: Visual cost estimates and resource management

### For Technical Teams
**The Technical Benefits:**
- **Visual Validation**: See configurations before applying them
- **Integrated Monitoring**: Built-in CloudWatch integration
- **Error Prevention**: Console validates configurations automatically
- **Documentation**: Console actions create automatic documentation
- **Rollback Capability**: Easy to revert changes through the console

## Deployment Strategy Overview

### What We're Building and Why

1. **High-Availability Web Tier**: Multiple servers across different zones
2. **Intelligent Load Balancing**: Automatic traffic distribution and health monitoring
3. **Auto Scaling**: Dynamic capacity adjustment based on demand
4. **Comprehensive Monitoring**: Full visibility into application performance
5. **Database High Availability**: Multi-AZ database with automatic failover

## Phase 1: Infrastructure Foundation

### Why Start with Infrastructure?
**Business Rationale:** Building a solid foundation prevents costly rework and ensures scalability from day one.

**Technical Rationale:** Infrastructure components depend on each other, so proper sequencing prevents configuration conflicts.

#### Step 1: Create VPC and Networking

**What you're doing:** Setting up the network foundation for your application
**Why this matters:** Proper networking ensures security, performance, and scalability

1. **Navigate to VPC Console**
   - AWS Console → VPC → "Create VPC"
   - *Why VPC first*: All other resources need a network to live in

2. **Configure VPC Settings**
   - Name: `lamp-stack-vpc`
   - IPv4 CIDR: `10.0.0.0/16`
   - *Why this CIDR*: Provides 65,536 IP addresses for future growth
   - *Business impact*: Ensures network capacity for business expansion

3. **Create Public Subnets**
   - Subnet 1: `lamp-stack-public-1a` (10.0.1.0/24) in us-east-1a
   - Subnet 2: `lamp-stack-public-1b` (10.0.2.0/24) in us-east-1b
   - *Why two subnets*: High availability requires resources in multiple zones
   - *Why public*: Load balancer needs internet access
   - *Business impact*: Ensures service remains available if one data center fails

4. **Create Private Subnets**
   - Subnet 1: `lamp-stack-private-1a` (10.0.3.0/24) in us-east-1a
   - Subnet 2: `lamp-stack-private-1b` (10.0.4.0/24) in us-east-1b
   - *Why private*: Database servers should not be directly accessible from internet
   - *Security benefit*: Reduces attack surface and improves security posture

5. **Configure Internet Gateway**
   - Create and attach Internet Gateway: `lamp-stack-igw`
   - *Why needed*: Enables internet access for public subnets
   - *Business impact*: Allows customers to reach your application

#### Step 2: Create Security Groups

**What you're doing:** Setting up firewall rules for different components
**Why security groups matter:** They control network access and are your first line of defense

1. **Create Load Balancer Security Group**
   - Name: `lamp-stack-alb-sg`
   - Inbound rules:
     - HTTP (80) from 0.0.0.0/0
     - HTTPS (443) from 0.0.0.0/0
   - *Why allow from anywhere*: Customers need to access your website
   - *Security note*: Only web ports are open, not management ports

2. **Create Web Server Security Group**
   - Name: `lamp-stack-web-sg`
   - Inbound rules:
     - HTTP (80) from ALB security group only
     - SSH (22) from your IP address only
   - *Why restrict HTTP*: Only load balancer should talk directly to web servers
   - *Why restrict SSH*: Only administrators should have shell access
   - *Security benefit*: Prevents direct attacks on web servers

3. **Create Database Security Group**
   - Name: `lamp-stack-db-sg`
   - Inbound rules:
     - MySQL (3306) from web server security group only
   - *Why restrict to web servers*: Database should only accept connections from application
   - *Security benefit*: Prevents unauthorized database access

### Phase 2: Database Setup

#### Why Database First?
**Business Rationale:** Database is the foundation of your application - web servers are useless without data.

**Technical Rationale:** Database setup takes time and web servers need database connection details.

#### Step 1: Create RDS Subnet Group

**What you're doing:** Defining where your database can be placed
**Why subnet groups matter:** Ensures database is in private subnets and highly available

1. **Navigate to RDS Console**
   - AWS Console → RDS → "Subnet groups" → "Create DB subnet group"
   - *Why RDS console*: Centralized database management

2. **Configure Subnet Group**
   - Name: `lamp-stack-db-subnet-group`
   - VPC: Select your `lamp-stack-vpc`
   - Availability Zones: us-east-1a, us-east-1b
   - Subnets: Select both private subnets
   - *Why private subnets*: Database should not be accessible from internet
   - *Why multiple AZs*: Enables automatic failover for high availability

#### Step 2: Create RDS MySQL Instance

**What you're doing:** Setting up your application's database
**Why RDS over self-managed:** AWS handles backups, updates, and maintenance automatically

1. **Create Database**
   - RDS Console → "Databases" → "Create database"
   - Engine: MySQL 8.0
   - *Why MySQL 8.0*: Latest stable version with best performance and security

2. **Configure Database Settings**
   - Template: Production (for high availability)
   - DB instance identifier: `lamp-stack-db`
   - Master username: `admin`
   - Master password: (create strong password)
   - *Why production template*: Includes best practices for reliability
   - *Security note*: Strong passwords prevent unauthorized access

3. **Configure Instance Specifications**
   - Instance class: db.t3.micro (for testing) or db.t3.small (for production)
   - Storage: 20 GB General Purpose SSD
   - Enable storage autoscaling: Yes, up to 100 GB
   - *Why autoscaling*: Prevents running out of storage space
   - *Business benefit*: Avoids downtime from storage issues

4. **Configure High Availability**
   - Multi-AZ deployment: Yes
   - *Why Multi-AZ*: Automatic failover if primary database fails
   - *Business impact*: Minimizes downtime from database failures
   - *Cost consideration*: Doubles database cost but provides business continuity

5. **Configure Connectivity**
   - VPC: `lamp-stack-vpc`
   - Subnet group: `lamp-stack-db-subnet-group`
   - Security group: `lamp-stack-db-sg`
   - Public access: No
   - *Why no public access*: Database should only be accessible from application servers
   - *Security benefit*: Eliminates direct internet attacks on database

6. **Configure Monitoring**
   - Enable Performance Insights: Yes
   - Retention period: 7 days (free tier)
   - Enable Enhanced monitoring: Yes
   - *Why monitoring*: Early detection of performance issues
   - *Business benefit*: Prevents database problems from affecting users

### Phase 3: Application Server Setup

#### Why Application Servers Next?
**Business Rationale:** Need working application servers before setting up load balancing and scaling.

**Technical Rationale:** Load balancer needs healthy targets to distribute traffic to.

#### Step 1: Create Launch Template

**What you're doing:** Defining the configuration for your web servers
**Why launch templates:** Ensures consistent server configuration and enables auto scaling

1. **Navigate to EC2 Console**
   - AWS Console → EC2 → "Launch Templates" → "Create launch template"
   - *Why launch templates*: Standardizes server configuration

2. **Configure Template Basics**
   - Name: `lamp-stack-web-template`
   - Description: "LAMP stack web server template"
   - *Why descriptive names*: Makes management easier for teams

3. **Configure AMI and Instance Type**
   - AMI: Ubuntu Server 22.04 LTS
   - Instance type: t2.micro (free tier) or t2.small (production)
   - *Why Ubuntu*: Stable, well-supported, and widely used
   - *Why these instance types*: Good balance of performance and cost

4. **Configure Key Pair and Security**
   - Key pair: Select your existing key pair
   - Security groups: `lamp-stack-web-sg`
   - *Why key pairs*: Secure access without passwords
   - *Security benefit*: Only authorized users can access servers

5. **Configure User Data Script**
   ```bash
   #!/bin/bash
   # Update system
   apt update -y
   apt upgrade -y
   
   # Install LAMP stack
   apt install -y apache2 php libapache2-mod-php php-mysql mysql-client
   
   # Start and enable Apache
   systemctl start apache2
   systemctl enable apache2
   
   # Configure Apache
   a2enmod rewrite
   systemctl restart apache2
   
   # Create simple health check
   echo "<?php echo 'OK'; ?>" > /var/www/html/health.php
   
   # Set permissions
   chown -R www-data:www-data /var/www/html
   chmod -R 755 /var/www/html
   ```
   - *Why user data*: Automatically configures servers without manual setup
   - *Business benefit*: Reduces deployment time and human error
   - *Why health check*: Load balancer needs to verify server health

#### Step 2: Launch Initial Instance

**What you're doing:** Creating your first web server to test configuration
**Why start with one:** Easier to troubleshoot and validate before scaling

1. **Launch Instance from Template**
   - EC2 Console → "Instances" → "Launch instances"
   - Select "Launch from template"
   - Choose your `lamp-stack-web-template`
   - *Why from template*: Ensures consistent configuration

2. **Configure Instance Details**
   - Subnet: `lamp-stack-public-1a` (for initial testing)
   - Auto-assign public IP: Yes
   - *Why public subnet initially*: Easier to test and configure
   - *Note*: Will move to private subnets later for security

3. **Test Instance**
   - Wait for instance to reach "running" state
   - Test health check: `http://instance-public-ip/health.php`
   - Should return "OK"
   - *Why test first*: Validates configuration before proceeding

### Phase 4: Load Balancer Setup

#### Why Load Balancer is Critical
**Business Rationale:** Load balancer provides high availability and better user experience through intelligent traffic distribution.

**Technical Rationale:** Single point of entry enables monitoring, SSL termination, and scaling.

#### Step 1: Create Application Load Balancer

**What you're doing:** Setting up intelligent traffic distribution
**Why ALB over other options:** Application Load Balancer provides advanced routing and integrates with AWS services

1. **Navigate to Load Balancer Console**
   - EC2 Console → "Load Balancers" → "Create Load Balancer"
   - Choose "Application Load Balancer"
   - *Why Application Load Balancer*: Designed for web applications with advanced features

2. **Configure Basic Settings**
   - Name: `lamp-stack-alb`
   - Scheme: Internet-facing
   - IP address type: IPv4
   - *Why internet-facing*: Customers need to access from internet
   - *Why IPv4*: Standard for most applications

3. **Configure Network Mapping**
   - VPC: `lamp-stack-vpc`
   - Availability Zones: 
     - us-east-1a: `lamp-stack-public-1a`
     - us-east-1b: `lamp-stack-public-1b`
   - *Why multiple AZs*: High availability requires presence in multiple zones
   - *Why public subnets*: Load balancer needs internet access

4. **Configure Security Groups**
   - Security group: `lamp-stack-alb-sg`
   - *Why specific security group*: Controls what traffic can reach load balancer

#### Step 2: Create Target Group

**What you're doing:** Defining which servers can receive traffic from the load balancer
**Why target groups:** Enables health checking and traffic distribution policies

1. **Create Target Group**
   - Target type: Instances
   - Name: `lamp-stack-web-targets`
   - Protocol: HTTP
   - Port: 80
   - VPC: `lamp-stack-vpc`
   - *Why HTTP*: Standard web protocol
   - *Why port 80*: Standard web server port

2. **Configure Health Checks**
   - Health check path: `/health.php`
   - Health check interval: 30 seconds
   - Healthy threshold: 2 consecutive successes
   - Unhealthy threshold: 5 consecutive failures
   - Timeout: 5 seconds
   - *Why /health.php*: Simple endpoint that confirms server is working
   - *Why these thresholds*: Balance between quick detection and avoiding false alarms
   - *Business impact*: Ensures traffic only goes to healthy servers

3. **Register Initial Target**
   - Select your test EC2 instance
   - Port: 80
   - *Why register now*: Validates health check configuration

#### Step 3: Configure Load Balancer Listener

**What you're doing:** Defining how the load balancer handles incoming requests
**Why listeners matter:** They determine how traffic is routed to your application

1. **Configure Default Listener**
   - Protocol: HTTP
   - Port: 80
   - Default action: Forward to `lamp-stack-web-targets`
   - *Why HTTP initially*: Simpler to set up and test
   - *Note*: HTTPS should be added for production

2. **Test Load Balancer**
   - Wait for load balancer to become "active"
   - Test: `http://load-balancer-dns-name/health.php`
   - Should return "OK"
   - *Why test*: Validates entire traffic flow

### Phase 5: Auto Scaling Setup

#### Why Auto Scaling is Essential
**Business Rationale:** Handles traffic spikes automatically without manual intervention, ensuring consistent user experience.

**Technical Rationale:** Provides elasticity to match capacity with demand, optimizing both performance and cost.

#### Step 1: Create Auto Scaling Group

**What you're doing:** Setting up automatic server management
**Why auto scaling:** Ensures you have the right number of servers for current demand

1. **Navigate to Auto Scaling Console**
   - EC2 Console → "Auto Scaling Groups" → "Create Auto Scaling group"
   - *Why Auto Scaling*: Automatically manages server capacity

2. **Configure Auto Scaling Group**
   - Name: `lamp-stack-asg`
   - Launch template: `lamp-stack-web-template`
   - Version: Latest
   - *Why launch template*: Ensures all servers have identical configuration

3. **Configure Network**
   - VPC: `lamp-stack-vpc`
   - Subnets: Both private subnets (`lamp-stack-private-1a`, `lamp-stack-private-1b`)
   - *Why private subnets*: Better security - servers not directly accessible from internet
   - *Why both subnets*: High availability across multiple zones

4. **Configure Load Balancing**
   - Attach to existing load balancer
   - Target group: `lamp-stack-web-targets`
   - Health check type: ELB (load balancer health checks)
   - Health check grace period: 300 seconds
   - *Why ELB health checks*: More comprehensive than EC2-only checks
   - *Why 300 seconds*: Gives servers time to fully start up

5. **Configure Group Size**
   - Desired capacity: 2
   - Minimum capacity: 1
   - Maximum capacity: 4
   - *Why start with 2*: Provides immediate high availability
   - *Why minimum 1*: Ensures service never completely stops
   - *Why maximum 4*: Reasonable limit for cost control

#### Step 2: Create Scaling Policies

**What you're doing:** Defining when and how to add or remove servers
**Why scaling policies:** Automate capacity decisions based on actual demand

1. **Create Scale-Out Policy**
   - Policy type: Target tracking scaling
   - Metric: Average CPU Utilization
   - Target value: 70%
   - *Why 70%*: Provides headroom for traffic spikes while triggering scaling
   - *Business benefit*: Maintains performance during busy periods

2. **Create Scale-In Policy**
   - Automatically created with scale-out policy
   - Cooldown: 300 seconds
   - *Why cooldown*: Prevents rapid scaling up and down
   - *Cost benefit*: Avoids unnecessary instance launches

### Phase 6: Monitoring and Alerting Setup

#### Why Comprehensive Monitoring
**Business Rationale:** Proactive monitoring prevents issues from becoming outages that cost revenue and damage reputation.

**Technical Rationale:** Visibility into system behavior enables optimization and troubleshooting.

#### Step 1: Create CloudWatch Dashboard

**What you're doing:** Building a visual overview of your entire system
**Why dashboards matter:** Centralized view enables quick assessment of system health

1. **Create Main Dashboard**
   - CloudWatch Console → "Dashboards" → "Create dashboard"
   - Name: `LAMP-Stack-Overview`
   - *Purpose*: Single pane of glass for system health

2. **Add Key Metrics Widgets**
   - **Load Balancer Request Count**
     - Shows traffic volume and patterns
     - *Business value*: Understand customer usage patterns
   
   - **Target Response Time**
     - Shows application performance
     - *Business value*: Ensure good user experience
   
   - **Healthy Host Count**
     - Shows available server capacity
     - *Business value*: Ensure adequate capacity
   
   - **Auto Scaling Group Size**
     - Shows scaling activity
     - *Business value*: Understand cost and capacity trends

#### Step 2: Create Critical Alerts

**What you're doing:** Setting up automatic notifications for important events
**Why alerting matters:** Enables rapid response to issues before they impact customers

1. **High Response Time Alert**
   - Metric: Target Response Time > 2 seconds
   - Period: 2 consecutive periods of 1 minute
   - Action: Send SNS notification
   - *Why 2 seconds*: User experience degrades significantly after this point
   - *Business impact*: Maintain competitive response times

2. **Low Healthy Host Count Alert**
   - Metric: Healthy Host Count < 1
   - Period: 1 minute
   - Action: Send SNS notification
   - *Why < 1*: No healthy servers means service is down
   - *Business impact*: Immediate notification of service outage

3. **High Error Rate Alert**
   - Metric: HTTP 5XX errors > 10 per minute
   - Period: 2 consecutive periods
   - Action: Send SNS notification
   - *Why 10 errors*: Indicates systemic problem
   - *Business impact*: Early detection of application issues

### Phase 7: Application Deployment

#### Why Application Deployment Last
**Business Rationale:** Infrastructure must be stable and tested before deploying business logic.

**Technical Rationale:** Application deployment is easier when infrastructure is already validated.

#### Step 1: Deploy Application Files

**What you're doing:** Getting your PHP application onto the web servers
**Why manual deployment initially:** Validates the process before automation

1. **Connect to Web Server**
   - Use Systems Manager Session Manager (preferred) or SSH
   - *Why Session Manager*: No need to manage SSH keys or bastion hosts
   - *Security benefit*: All access is logged and auditable

2. **Deploy PHP Files**
   ```bash
   # Navigate to web directory
   cd /var/www/html
   
   # Create application structure
   sudo mkdir -p {public,includes,assets}
   
   # Deploy your PHP files
   # (Upload files via SCP or copy from S3)
   
   # Set proper permissions
   sudo chown -R www-data:www-data /var/www/html
   sudo chmod -R 755 /var/www/html
   ```
   - *Why proper permissions*: Ensures web server can read files
   - *Security note*: Restrictive permissions prevent unauthorized access

3. **Configure Database Connection**
   - Update configuration files with RDS endpoint
   - Use environment variables for sensitive data
   - *Why environment variables*: Keeps secrets out of code
   - *Security benefit*: Easier to rotate credentials

#### Step 2: Test Complete Application

**What you're doing:** Validating that all components work together
**Why end-to-end testing:** Individual components may work but integration might fail

1. **Test Through Load Balancer**
   - Access application via load balancer DNS name
   - Test all major functions: homepage, user creation, API
   - *Why through load balancer*: Tests the complete user path

2. **Test High Availability**
   - Stop one EC2 instance
   - Verify application still works
   - Verify auto scaling replaces the instance
   - *Why test failover*: Validates high availability configuration

3. **Test Auto Scaling**
   - Generate load (using CloudWatch Synthetics)
   - Verify new instances launch automatically
   - Verify performance remains good
   - *Why test scaling*: Ensures system handles growth

## Post-Deployment Validation

### Functional Testing Checklist

**Application Functionality**
- [ ] Homepage loads correctly
- [ ] User creation works
- [ ] Database queries return data
- [ ] API endpoints respond correctly
- [ ] Health checks return "OK"

**Infrastructure Functionality**
- [ ] Load balancer distributes traffic
- [ ] Auto scaling launches new instances
- [ ] Database failover works (test in maintenance window)
- [ ] Monitoring alerts trigger correctly
- [ ] All security groups allow required traffic only

### Performance Validation

**Response Time Targets**
- Homepage: < 500ms
- API calls: < 200ms
- Database queries: < 100ms

**Availability Targets**
- Overall uptime: > 99.9%
- Error rate: < 0.1%

**Scalability Validation**
- Auto scaling triggers at 70% CPU
- New instances become healthy within 5 minutes
- Performance remains good during scaling events

## Maintenance and Operations

### Daily Operations
- Review CloudWatch dashboard for anomalies
- Check auto scaling activity
- Monitor error rates and response times

### Weekly Operations
- Review and analyze performance trends
- Check for security updates
- Validate backup and recovery procedures

### Monthly Operations
- Review and optimize costs
- Update documentation
- Test disaster recovery procedures
- Review and update monitoring thresholds

This comprehensive, console-based deployment approach ensures your LAMP stack application is highly available, scalable, and maintainable while following AWS best practices for security and performance.