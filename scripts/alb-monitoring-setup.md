# Application Load Balancer Monitoring Setup

## Issue: "Create Alarm" Button Not Visible

The AWS Console interface has changed, and the "Create alarm" button is no longer directly available in the ALB Monitoring tab. Instead, you need to create CloudWatch alarms through the CloudWatch service.

## Solution: Create Alarms via CloudWatch Console

### Step 1: Navigate to CloudWatch Console

1. Go to **AWS Management Console**
2. Search for **CloudWatch** and click on it
3. In the left navigation pane, click **Alarms** → **All alarms**
4. Click **Create alarm**

### Step 2: Create Target Response Time Alarm

1. **Select Metric:**
   - Click **Select metric**
   - Choose **ApplicationELB** → **Per AppELB Metrics**
   - Find your ALB name and select **TargetResponseTime**
   - Click **Select metric**

2. **Configure Conditions:**
   - Statistic: **Average**
   - Period: **1 minute**
   - Threshold type: **Static**
   - Condition: **Greater than** `2` (seconds)

3. **Configure Actions:**
   - Alarm state trigger: **In alarm**
   - Select SNS topic or create new one
   - Add email endpoint for notifications

4. **Add Name and Description:**
   - Alarm name: `ALB-High-Response-Time`
   - Description: `ALB target response time exceeds 2 seconds`

### Step 3: Create HTTP 5XX Errors Alarm

1. **Select Metric:**
   - Click **Create alarm** → **Select metric**
   - Choose **ApplicationELB** → **Per AppELB Metrics**
   - Find your ALB and select **HTTPCode_ELB_5XX_Count**
   - Click **Select metric**

2. **Configure Conditions:**
   - Statistic: **Sum**
   - Period: **1 minute**
   - Threshold type: **Static**
   - Condition: **Greater than** `5`

3. **Configure Actions:**
   - Use same SNS topic as previous alarm

4. **Add Name and Description:**
   - Alarm name: `ALB-HTTP-5XX-Errors`
   - Description: `ALB HTTP 5XX errors exceed 5 per minute`

### Step 4: Create Unhealthy Host Count Alarm

1. **Select Metric:**
   - Click **Create alarm** → **Select metric**
   - Choose **ApplicationELB** → **Per AppELB, per TG Metrics**
   - Find your ALB and Target Group
   - Select **UnHealthyHostCount**
   - Click **Select metric**

2. **Configure Conditions:**
   - Statistic: **Average**
   - Period: **1 minute**
   - Threshold type: **Static**
   - Condition: **Greater than or equal to** `1`

3. **Configure Actions:**
   - Use same SNS topic

4. **Add Name and Description:**
   - Alarm name: `ALB-Unhealthy-Hosts`
   - Description: `ALB has 1 or more unhealthy targets`

## Alternative: Using AWS CLI

If you prefer command line, you can create these alarms using AWS CLI:

```bash
# Create SNS topic first
aws sns create-topic --name alb-monitoring-alerts

# Subscribe email to topic (replace with your email)
aws sns subscribe --topic-arn arn:aws:sns:region:account:alb-monitoring-alerts --protocol email --notification-endpoint your-email@example.com

# Create Target Response Time Alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "ALB-High-Response-Time" \
    --alarm-description "ALB target response time exceeds 2 seconds" \
    --metric-name TargetResponseTime \
    --namespace AWS/ApplicationELB \
    --statistic Average \
    --period 60 \
    --threshold 2 \
    --comparison-operator GreaterThanThreshold \
    --dimensions Name=LoadBalancer,Value=app/your-alb-name/1234567890abcdef \
    --evaluation-periods 2 \
    --alarm-actions arn:aws:sns:region:account:alb-monitoring-alerts

# Create HTTP 5XX Errors Alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "ALB-HTTP-5XX-Errors" \
    --alarm-description "ALB HTTP 5XX errors exceed 5 per minute" \
    --metric-name HTTPCode_ELB_5XX_Count \
    --namespace AWS/ApplicationELB \
    --statistic Sum \
    --period 60 \
    --threshold 5 \
    --comparison-operator GreaterThanThreshold \
    --dimensions Name=LoadBalancer,Value=app/your-alb-name/1234567890abcdef \
    --evaluation-periods 1 \
    --alarm-actions arn:aws:sns:region:account:alb-monitoring-alerts

# Create Unhealthy Host Count Alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "ALB-Unhealthy-Hosts" \
    --alarm-description "ALB has 1 or more unhealthy targets" \
    --metric-name UnHealthyHostCount \
    --namespace AWS/ApplicationELB \
    --statistic Average \
    --period 60 \
    --threshold 1 \
    --comparison-operator GreaterThanOrEqualToThreshold \
    --dimensions Name=TargetGroup,Value=targetgroup/your-tg-name/1234567890abcdef Name=LoadBalancer,Value=app/your-alb-name/1234567890abcdef \
    --evaluation-periods 1 \
    --alarm-actions arn:aws:sns:region:account:alb-monitoring-alerts
```

## Key Metrics to Monitor

### 1. TargetResponseTime
- **Purpose:** Monitor application performance
- **Threshold:** 2 seconds
- **Action:** Scale out if consistently high

### 2. HTTPCode_ELB_5XX_Count
- **Purpose:** Detect server errors
- **Threshold:** 5 errors per minute
- **Action:** Investigate application issues

### 3. UnHealthyHostCount
- **Purpose:** Monitor target health
- **Threshold:** 1 or more unhealthy targets
- **Action:** Check instance health, auto-scaling

### 4. Additional Recommended Metrics

- **RequestCount:** Monitor traffic volume
- **HTTPCode_Target_4XX_Count:** Client errors
- **ActiveConnectionCount:** Current connections
- **NewConnectionCount:** New connections per second

## Verification Steps

1. **Check Alarm Status:**
   - Go to CloudWatch → Alarms
   - Verify all alarms show "OK" status initially

2. **Test Alarms:**
   - Simulate high load or stop instances
   - Verify alarms trigger and notifications are sent

3. **Monitor Dashboard:**
   - Create CloudWatch dashboard with ALB metrics
   - Add widgets for key metrics

## Troubleshooting

### Common Issues:

1. **Metrics Not Appearing:**
   - Ensure ALB has received traffic
   - Wait 5-10 minutes for metrics to populate

2. **Alarms Not Triggering:**
   - Check evaluation periods and datapoints
   - Verify threshold values are appropriate

3. **No Email Notifications:**
   - Confirm SNS subscription
   - Check spam folder
   - Verify email endpoint

### Best Practices:

- Set appropriate evaluation periods (2-3 datapoints)
- Use different notification channels for different severity levels
- Create composite alarms for complex conditions
- Regularly review and adjust thresholds based on application behavior