<?php
// Include configuration file
require_once 'config.php';

// Start session for visit counter
session_start();

// Counter for page visits (stored in session)
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count'] = 1;
} else {
    $_SESSION['visit_count']++;
}

// Function to test database connection
function testDbConnection($host, $user, $pass, $db) {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        return "Database connection failed: " . $conn->connect_error;
    }
    
    // Count users in database
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    $userCount = $row['count'];
    
    $conn->close();
    return "Database connection successful! Found $userCount users in database.";
}

// Get server information
$server_info = $_SERVER['SERVER_ADDR'] . ' (' . gethostname() . ')';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAMP Stack Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0066cc;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e9f7fe;
            border-left: 4px solid #0066cc;
            padding: 10px 15px;
            margin: 15px 0;
        }
        .success {
            background: #e7f7e7;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #ffebee;
            border-left: 4px solid #dc3545;
        }
        .feature-box {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .feature {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .feature h3 {
            margin-top: 0;
            color: #0066cc;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #0052a3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LAMP Stack Application</h1>
        
        <div class="info-box">
            <p><strong>Server:</strong> <?php echo $server_info; ?></p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Visit Count:</strong> <?php echo $_SESSION['visit_count']; ?></p>
        </div>
        
        <h2>Database Connection Test</h2>
        <div class="info-box <?php echo strpos(testDbConnection($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']), 'successful') ? 'success' : 'error'; ?>">
            <?php echo testDbConnection($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']); ?>
        </div>
        
        <h2>Application Features</h2>
        <div class="feature-box">
            <div class="feature">
                <h3>User Management</h3>
                <p>Create, view, and manage users in the database.</p>
                <a href="create_user.php" class="btn">Create User</a>
                <a href="view_users.php" class="btn">View Users</a>
            </div>
            
            <div class="feature">
                <h3>API Access</h3>
                <p>Access the application data via REST API.</p>
                <a href="api_test.html" class="btn">API Test Page</a>
            </div>
            
            <div class="feature">
                <h3>System Info</h3>
                <p>View detailed PHP and server information.</p>
                <a href="info.php" class="btn">View Info</a>
            </div>
        </div>
        
        <h2>System Information</h2>
        <div class="info-box">
            <p><strong>Date/Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        </div>
    </div>
</body>
</html>