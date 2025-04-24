<?php
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;

require_once '../includes/config.php';
require_once '../../app/config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Test connection
echo "Testing database connection...\n";
if ($conn) {
    echo "Database connected successfully\n";
} else {
    echo "Database connection failed\n";
    exit;
}

// List all users
echo "\nListing all users:\n";
try {
    $stmt = $conn->query("SELECT id, email, role FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error listing users: " . $e->getMessage() . "\n";
}

// Test deleting a user
$user_id_to_delete = 2; // Change this to the ID you want to test
echo "\nTrying to delete user ID: " . $user_id_to_delete . "\n";

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$user_id_to_delete]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "User not found\n";
        exit;
    }
    
    echo "User found. Role: " . $user['role'] . "\n";
    
    // Try to delete
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$user_id_to_delete]);
    
    echo "Delete execution result: " . ($result ? "Success" : "Failed") . "\n";
    echo "Rows affected: " . $stmt->rowCount() . "\n";
    
    if (!$result) {
        echo "Error info: ";
        print_r($stmt->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "Error deleting user: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
} 