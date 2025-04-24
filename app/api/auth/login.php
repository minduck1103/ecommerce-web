<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log the POST data
    error_log("Login POST data: " . print_r($_POST, true));

    // Validate input
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        throw new Exception("Vui lòng điền đầy đủ thông tin đăng nhập.");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Log the processed data
    error_log("Processed login data - Email: $email");

    // Khởi tạo kết nối database
    $database = new Database();
    $conn = $database->getConnection();

    // Get user from database using PDO
    $stmt = $conn->prepare("SELECT id, username, full_name, email, password, role FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Email hoặc mật khẩu không chính xác.");
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("Password verification failed for user: " . $user['email']);
        throw new Exception("Email hoặc mật khẩu không chính xác.");
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Get redirect URL if set, otherwise use default
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '/shoppingcart';

    // Log successful login
    error_log("Successful login for user: " . $user['email']);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công!',
        'redirect_url' => $redirect_url
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 