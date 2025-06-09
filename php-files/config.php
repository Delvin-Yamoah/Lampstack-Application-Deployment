<?php
// Configuration file for database connection

// Use environment variables if available, otherwise use defaults
// In production, set these as environment variables on your EC2 instances
$config = [
    'db_host' => getenv('DB_HOST') ?: 'your-rds-endpoint.rds.amazonaws.com',
    'db_user' => getenv('DB_USER') ?: 'admin',
    'db_pass' => getenv('DB_PASS') ?: 'your-password',
    'db_name' => getenv('DB_NAME') ?: 'myapp'
];
?>