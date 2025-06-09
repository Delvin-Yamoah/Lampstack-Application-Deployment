<?php
// Database setup script - Run this once to initialize your database

// Database connection parameters
$db_host = getenv('DB_HOST') ?: 'your-rds-endpoint.rds.amazonaws.com';
$db_user = getenv('DB_USER') ?: 'admin';
$db_pass = getenv('DB_PASS') ?: 'your-password';
$db_name = getenv('DB_NAME') ?: 'myapp';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($db_name);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert sample data
$sql = "INSERT INTO users (name, email) VALUES
    ('John Doe', 'john@example.com'),
    ('Jane Smith', 'jane@example.com'),
    ('Bob Johnson', 'bob@example.com')";

if ($conn->query($sql) === TRUE) {
    echo "Sample data inserted successfully<br>";
} else {
    // If error is duplicate entry, that's okay
    if (strpos($conn->error, 'Duplicate') !== false) {
        echo "Sample data already exists<br>";
    } else {
        echo "Error inserting sample data: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Database setup complete!";
?>