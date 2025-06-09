<?php
// Include configuration file
require_once 'config.php';

// Set headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Create database connection
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// GET request - List all users
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    
    if ($result) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['success' => true, 'users' => $users]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch users: ' . $conn->error]);
    }
}

// POST request - Create new user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a form submission or JSON data
    if (isset($_POST['name']) && isset($_POST['email'])) {
        // Form submission
        $name = $_POST['name'];
        $email = $_POST['email'];
    } else {
        // JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['name']) || !isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and email are required']);
            exit();
        }
        
        $name = $data['name'];
        $email = $data['email'];
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit();
    }
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'User created successfully',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create user: ' . $stmt->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>