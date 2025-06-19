# CloudWatch Synthetics Canary Troubleshooting

## Current Error Analysis

The error shows your canary `lamp-stack-api-monitor` is failing with:
```
Exception: Failed: Not Found
```

This indicates the canary is trying to access a URL that returns HTTP 404.

## Immediate Fix Steps

### 1. Check Your Load Balancer URL

First, verify your ALB is working:

```bash
# Replace with your actual ALB DNS name
curl -v http://your-alb-dns-name.region.elb.amazonaws.com/healthcheck.php
```

Expected response: `OK`

### 2. Test API Endpoint Directly

```bash
# Test the API endpoint
curl -H "Accept: application/json" http://your-alb-dns-name.region.elb.amazonaws.com/api.php
```

Expected response: JSON with users data

### 3. Update Canary Configuration

In your CloudWatch Synthetics console:

1. Go to **CloudWatch** → **Synthetics** → **Canaries**
2. Find `lamp-stack-api-monitor`
3. Click **Actions** → **Edit**
4. Update the script with the correct ALB URL
5. Use the fixed script provided above

### 4. Common URL Issues

Check these common problems:

- **Wrong protocol**: Ensure using `http://` not `https://` (unless you have SSL)
- **Missing trailing slash**: Some configurations require `/` at the end
- **Incorrect path**: Verify `/api.php` exists and is accessible
- **Case sensitivity**: Ensure file names match exactly

## Quick Verification Commands

Run these on your EC2 instance to verify files are deployed:

```bash
# Check if files exist
ls -la /var/www/html/

# Test locally on the instance
curl localhost/api.php
curl localhost/healthcheck.php

# Check Apache error logs
sudo tail -f /var/log/apache2/error.log
```

## Fix the Canary Script

Replace your current canary script with this minimal version:

```python
import urllib3
import json

def handler(event, context):
    # Replace with your actual ALB DNS name
    base_url = "http://your-alb-dns-name.region.elb.amazonaws.com"
    
    http = urllib3.PoolManager()
    
    # Test health check
    response = http.request('GET', f"{base_url}/healthcheck.php")
    if response.status != 200:
        raise Exception(f"Health check failed: {response.status}")
    
    # Test API
    response = http.request('GET', f"{base_url}/api.php")
    if response.status != 200:
        raise Exception(f"API check failed: {response.status}")
    
    return {"statusCode": 200, "body": "All tests passed"}
```

## Target Group Health Check

Ensure your target group health check is configured correctly:

1. **Health check path**: `/healthcheck.php`
2. **Health check port**: `80`
3. **Healthy threshold**: `2`
4. **Unhealthy threshold**: `2`
5. **Timeout**: `5 seconds`
6. **Interval**: `30 seconds`

## Debug Steps

### 1. Check Target Group Health

```bash
aws elbv2 describe-target-health --target-group-arn your-target-group-arn
```

### 2. Check ALB Listeners

```bash
aws elbv2 describe-listeners --load-balancer-arn your-alb-arn
```

### 3. Test from Different Locations

```bash
# Test from your local machine
curl -v http://your-alb-dns-name.region.elb.amazonaws.com/api.php

# Test from another AWS region
# Use CloudShell or another EC2 instance
```

## Most Likely Causes

1. **Incorrect ALB DNS name** in canary script
2. **Target instances are unhealthy** - check target group
3. **Security group blocking traffic** - verify port 80 is open
4. **PHP files not deployed** to `/var/www/html/`
5. **Apache not running** on target instances

## Quick Fix Command

Update your canary with the correct URL:

```bash
# Get your ALB DNS name
aws elbv2 describe-load-balancers --names LAMP-Stack-ALB --query 'LoadBalancers[0].DNSName' --output text
```

Then update the canary script with this exact URL.