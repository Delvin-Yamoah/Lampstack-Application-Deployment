<?php
// Application monitoring endpoint
// Access via: http://your-domain/monitoring.php

require_once '../includes/config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Function to get system metrics
function getSystemMetrics() {
    $metrics = [];
    
    // CPU Load Average
    $load = sys_getloadavg();
    $metrics['cpu_load'] = [
        '1min' => $load[0],
        '5min' => $load[1],
        '15min' => $load[2]
    ];
    
    // Memory Usage
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    
    $metrics['memory'] = [
        'total' => round($mem[1] / 1024, 2) . ' MB',
        'used' => round($mem[2] / 1024, 2) . ' MB',
        'free' => round($mem[3] / 1024, 2) . ' MB',
        'usage_percent' => round(($mem[2] / $mem[1]) * 100, 2)
    ];
    
    // Disk Usage
    $disk_total = disk_total_space('/');
    $disk_free = disk_free_space('/');
    $disk_used = $disk_total - $disk_free;
    
    $metrics['disk'] = [
        'total' => round($disk_total / (1024*1024*1024), 2) . ' GB',
        'used' => round($disk_used / (1024*1024*1024), 2) . ' GB',
        'free' => round($disk_free / (1024*1024*1024), 2) . ' GB',
        'usage_percent' => round(($disk_used / $disk_total) * 100, 2)
    ];
    
    return $metrics;
}

// Function to test database performance
function getDatabaseMetrics() {
    global $config;
    
    $metrics = [
        'connection_status' => 'unknown',
        'response_time_ms' => 0,
        'user_count' => 0,
        'connection_error' => null
    ];
    
    $start_time = microtime(true);
    
    try {
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        if ($conn->connect_error) {
            $metrics['connection_status'] = 'failed';
            $metrics['connection_error'] = $conn->connect_error;
        } else {
            $metrics['connection_status'] = 'success';
            
            // Test query performance
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            if ($result) {
                $row = $result->fetch_assoc();
                $metrics['user_count'] = (int)$row['count'];
            }
            
            // Test a more complex query
            $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
            
            $conn->close();
        }
    } catch (Exception $e) {
        $metrics['connection_status'] = 'error';
        $metrics['connection_error'] = $e->getMessage();
    }
    
    $metrics['response_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
    
    return $metrics;
}

// Function to get application metrics
function getApplicationMetrics() {
    return [
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        'server_info' => [
            'hostname' => gethostname(),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown'
        ],
        'php_metrics' => [
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ]
    ];
}

// Function to check service health
function getHealthStatus() {
    $health = [
        'overall_status' => 'healthy',
        'checks' => []
    ];
    
    // Check Apache
    $apache_status = shell_exec('systemctl is-active apache2 2>/dev/null');
    $health['checks']['apache'] = [
        'status' => trim($apache_status) === 'active' ? 'healthy' : 'unhealthy',
        'service' => 'apache2'
    ];
    
    // Check disk space (alert if > 90%)
    $disk_usage = round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100, 2);
    $health['checks']['disk_space'] = [
        'status' => $disk_usage < 90 ? 'healthy' : 'warning',
        'usage_percent' => $disk_usage
    ];
    
    // Check memory usage (alert if > 90%)
    $free = shell_exec('free');
    $free_arr = explode("\n", trim($free));
    $mem = array_filter(explode(" ", $free_arr[1]));
    $mem = array_merge($mem);
    $memory_usage = round(($mem[2] / $mem[1]) * 100, 2);
    
    $health['checks']['memory'] = [
        'status' => $memory_usage < 90 ? 'healthy' : 'warning',
        'usage_percent' => $memory_usage
    ];
    
    // Set overall status
    foreach ($health['checks'] as $check) {
        if ($check['status'] === 'unhealthy') {
            $health['overall_status'] = 'unhealthy';
            break;
        } elseif ($check['status'] === 'warning' && $health['overall_status'] === 'healthy') {
            $health['overall_status'] = 'warning';
        }
    }
    
    return $health;
}

// Main monitoring data collection
$monitoring_data = [
    'application' => getApplicationMetrics(),
    'system' => getSystemMetrics(),
    'database' => getDatabaseMetrics(),
    'health' => getHealthStatus()
];

// Output JSON response
echo json_encode($monitoring_data, JSON_PRETTY_PRINT);
?>