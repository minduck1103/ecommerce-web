<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test Page</h1>";
echo "<p>If you can see this, PHP is working correctly.</p>";

// Kiểm tra session
session_start();
echo "<h2>Session Information:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "</p>";

// Kiểm tra kết nối database
try {
    require_once __DIR__ . '/../../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "<h2>Database Connection:</h2>";
    echo "<p>Database connection successful!</p>";
    
    // Kiểm tra bảng orders
    $stmt = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Table 'orders' exists.</p>";
        
        // Đếm số đơn hàng
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        $count = $stmt->fetchColumn();
        echo "<p>Total orders in database: " . $count . "</p>";
    } else {
        echo "<p>Table 'orders' does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<h2>Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

// Hiển thị thông tin PHP
echo "<h2>PHP Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
?> 