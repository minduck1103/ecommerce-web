<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log the POST data
    error_log("Register POST data: " . print_r($_POST, true));

    // Validate input
    $required_fields = ['full_name', 'phone', 'email', 'password', 'confirm_password'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception("Vui lòng điền đầy đủ thông tin. Thiếu: " . implode(', ', $missing_fields));
    }

    $fullname = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Log the processed data
    error_log("Processed data - Name: $fullname, Phone: $phone, Email: $email");

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email không hợp lệ.");
    }

    // Validate phone number (Vietnam format)
    if (!preg_match('/^(0|\+84)[3|5|7|8|9][0-9]{8}$/', $phone)) {
        throw new Exception("Số điện thoại không hợp lệ.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        throw new Exception("Mật khẩu xác nhận không khớp.");
    }

    // Khởi tạo kết nối database
    $database = new Database();
    $conn = $database->getConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("Email đã được sử dụng.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate username from email (take part before @)
    $username = explode('@', $email)[0];
    $role = 'user'; // Set default role to 'user'

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, role) VALUES (:username, :password, :email, :fullname, :phone, :role)");
    
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':role', $role);
    
    if (!$stmt->execute()) {
        error_log("Execute insert failed: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.");
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Đăng ký thành công!',
        'redirect_url' => '/shoppingcart/auth/login'
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 