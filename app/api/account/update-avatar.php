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

// Kiểm tra file upload
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng chọn ảnh để tải lên'
    ]);
    exit;
}

// Kiểm tra loại file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)'
    ]);
    exit;
}

// Kiểm tra kích thước file (max 5MB)
if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
    echo json_encode([
        'success' => false,
        'message' => 'Kích thước file không được vượt quá 5MB'
    ]);
    exit;
}

// Tạo tên file mới
$file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$new_filename = uniqid() . '.' . $file_extension;

// Tạo thư mục nếu chưa tồn tại
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/shoppingcart/public/uploads/avatars/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Di chuyển file
$upload_path = $upload_dir . $new_filename;
if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tải file lên'
    ]);
    exit;
}

// Kết nối database
$database = new Database();
$conn = $database->getConnection();

try {
    // Lấy avatar cũ
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $old_avatar = $stmt->fetchColumn();

    // Xóa avatar cũ nếu tồn tại
    if ($old_avatar && file_exists($upload_dir . $old_avatar)) {
        unlink($upload_dir . $old_avatar);
    }

    // Cập nhật avatar mới
    $stmt = $conn->prepare("UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_filename, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật ảnh đại diện thành công',
        'avatar_url' => '/shoppingcart/public/uploads/avatars/' . $new_filename
    ]);
} catch (PDOException $e) {
    // Xóa file vừa upload nếu có lỗi
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }

    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
} 