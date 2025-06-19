# Performance Testing via AWS Management Console

## Overview

This guide shows how to perform comprehensive performance testing using only AWS Management Console services. No external tools or scripts required.

## Why Console-Based Testing?

### Business Benefits
- **Cost Effective**: Uses existing AWS services
- **Integrated Monitoring**: Results automatically appear in CloudWatch
- **No Additional Infrastructure**: No need to set up testing servers
- **Consistent Results**: Tests run from AWS infrastructure

### Technical Benefits
- **Built-in Reporting**: Automatic integration with CloudWatch
- **Global Testing**: Test from multiple AWS regions
- **Realistic Conditions**: Tests run from AWS network
- **Easy Automation**: Can be scheduled and repeated

## Testing Strategy

### 1. Baseline Performance Testing
**Tool**: CloudWatch Synthetics
**Purpose**: Establish performance baselines
**Frequency**: Continuous (every 5 minutes)

### 2. Load Testing
**Tool**: Multiple CloudWatch Synthetics Canaries
**Purpose**: Simulate concurrent users
**Frequency**: On-demand and scheduled

### 3. Auto Scaling Testing
**Tool**: Systems Manager + CloudWatch
**Purpose**: Verify scaling behavior
**Frequency**: Weekly validation

### 4. Database Performance Testing
**Tool**: RDS Performance Insights
**Purpose**: Monitor database under load
**Frequency**: During load tests

## Step-by-Step Testing Setup

### Phase 1: Create Performance Monitoring Canaries

#### Step 1: Basic Performance Canary
1. **CloudWatch Console** → **Synthetics** → **Create canary**
2. **Configuration**:
   - Name: `lamp-stack-performance-baseline`
   - Blueprint: Heartbeat monitoring
   - URL: Your ALB DNS name
   - Frequency: Every 5 minutes
   - Success criteria: Response time < 2 seconds

#### Step 2: Multi-Step User Journey Canary
1. **Create canary** → **API canary**
2. **Configuration**:
   - Name: `lamp-stack-user-journey`
   - Test multiple endpoints in sequence
   - Frequency: Every 10 minutes

### Phase 2: Load Testing Setup

#### Step 1: Create Regional Load Canaries
1. **Create canaries in multiple regions**:
   - US East (N. Virginia): `lamp-load-east`
   - US West (Oregon): `lamp-load-west`
   - Europe (Ireland): `lamp-load-eu`

2. **Configure for load testing**:
   - Frequency: Every 1 minute
   - Stagger start times by 20 seconds
   - Monitor response times and success rates

#### Step 2: Monitor Load Test Results
1. **Create Load Testing Dashboard**
   - CloudWatch → Dashboards → Create dashboard
   - Add widgets for:
     - Canary success rates
     - Response times
     - EC2 CPU utilization
     - ALB request count

### Phase 3: Auto Scaling Testing

#### Step 1: Trigger Scaling via Systems Manager
1. **Systems Manager Console** → **Run Command**
2. **Document**: AWS-RunShellScript
3. **Targets**: Select one EC2 instance
4. **Command**: 
   ```bash
   # Generate CPU load for 10 minutes
   stress --cpu 2 --timeout 600s
   ```

#### Step 2: Monitor Scaling Behavior
1. **CloudWatch Dashboard** → Monitor:
   - Auto Scaling Group desired capacity
   - EC2 CPU utilization
   - ALB healthy host count
   - Response times during scaling

### Phase 4: Database Performance Testing

#### Step 1: Enable RDS Performance Insights
1. **RDS Console** → **Databases** → Select your database
2. **Modify** → Enable Performance Insights
3. **Configure** → 7-day retention (free tier)

#### Step 2: Monitor Database During Load Tests
1. **Performance Insights Dashboard** → Monitor:
   - Database load
   - Top SQL statements
   - Wait events
   - Connection count

## Testing Scenarios

### Scenario 1: Normal Load Validation
**Purpose**: Verify performance under typical usage
**Method**: Single canary running every 5 minutes
**Success Criteria**:
- Response time < 500ms
- Success rate > 99%
- CPU utilization < 50%

### Scenario 2: Peak Load Testing
**Purpose**: Test performance during busy periods
**Method**: Multiple regional canaries running every minute
**Success Criteria**:
- Response time < 1 second
- Success rate > 95%
- Auto scaling triggers appropriately

### Scenario 3: Stress Testing
**Purpose**: Find system breaking point
**Method**: Gradually increase canary frequency
**Success Criteria**:
- System fails gracefully
- Monitoring alerts trigger
- Recovery is automatic

## Performance Targets

### Response Time Targets
- **Homepage**: < 500ms (95th percentile)
- **API Calls**: < 200ms (95th percentile)
- **Database Queries**: < 100ms (average)

### Availability Targets
- **Uptime**: > 99.9%
- **Error Rate**: < 0.1% under normal load
- **Success Rate**: > 95% under peak load

### Scalability Targets
- **Scale-out Time**: < 5 minutes
- **Scale-in Time**: < 10 minutes
- **Concurrent Users**: 100+ simultaneous users

## Interpreting Results

### CloudWatch Synthetics Metrics
- **Duration**: Response time for each request
- **SuccessPercent**: Percentage of successful requests
- **Failed**: Number of failed requests

### Auto Scaling Metrics
- **GroupDesiredCapacity**: Target number of instances
- **GroupInServiceInstances**: Currently running instances
- **GroupTotalInstances**: All instances (including launching)

### Database Performance Metrics
- **DatabaseConnections**: Active database connections
- **CPUUtilization**: Database server CPU usage
- **ReadLatency/WriteLatency**: Database operation speed

## Troubleshooting Performance Issues

### High Response Times
1. **Check EC2 metrics**: CPU, memory utilization
2. **Check database metrics**: CPU, connections, latency
3. **Review ALB metrics**: Request distribution

### Auto Scaling Not Working
1. **Check CloudWatch alarms**: Verify alarm states
2. **Review scaling policies**: Confirm thresholds
3. **Check instance health**: Verify health check configuration

### Database Performance Issues
1. **Performance Insights**: Identify slow queries
2. **Connection monitoring**: Check for connection limits
3. **Resource utilization**: Monitor CPU and memory

This console-based approach provides comprehensive performance testing capabilities without requiring external tools or complex scripting.