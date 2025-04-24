<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này'
    ]);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit;
}

// Lấy dữ liệu từ form
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate dữ liệu
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng điền đầy đủ thông tin'
    ]);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Mật khẩu mới không khớp'
    ]);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'
    ]);
    exit;
}

// Kết nối database
$database = new Database();
$conn = $database->getConnection();

try {
    // Kiểm tra mật khẩu hiện tại
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current_password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu hiện tại không đúng'
        ]);
        exit;
    }

    // Cập nhật mật khẩu mới
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Đổi mật khẩu thành công'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
} 