<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    // Kiểm tra xác thực admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Không có quyền truy cập');
    }

    // Kiểm tra dữ liệu đầu vào
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        throw new Exception('Thiếu thông tin cần thiết');
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $is_admin = isset($_POST['is_admin']) ? intval($_POST['is_admin']) : 0;

    // Validate dữ liệu
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ');
    }

    if (empty($password)) {
        throw new Exception('Mật khẩu không được để trống');
    }

    // Kiểm tra email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email đã được sử dụng');
    }

    // Thêm người dùng mới
    $stmt = $conn->prepare("INSERT INTO users (email, password, username, phone, is_admin) VALUES (?, ?, ?, ?, ?)");
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    if (!$stmt->execute([$email, $hashedPassword, $username, $phone, $is_admin])) {
        throw new Exception('Không thể thêm người dùng');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thêm người dùng thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 