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
$full_name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$birthday = $_POST['birthday'] ?? null;
$address = $_POST['address'] ?? '';
$gender = $_POST['gender'] ?? null;

// Validate dữ liệu
if (empty($full_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập họ tên'
    ]);
    exit;
}

// Validate ngày sinh
if (!empty($birthday)) {
    $date = DateTime::createFromFormat('Y-m-d', $birthday);
    if (!$date || $date->format('Y-m-d') !== $birthday) {
        echo json_encode([
            'success' => false,
            'message' => 'Ngày sinh không hợp lệ'
        ]);
        exit;
    }
}

// Validate giới tính
if (!empty($gender) && !in_array($gender, ['male', 'female', 'other'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Giới tính không hợp lệ'
    ]);
    exit;
}

// Kết nối database
$database = new Database();
$conn = $database->getConnection();

try {
    // Cập nhật thông tin
    $stmt = $conn->prepare("
        UPDATE users 
        SET full_name = ?, 
            phone = ?, 
            birthday = ?, 
            address = ?, 
            gender = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $full_name,
        $phone,
        $birthday,
        $address,
        $gender,
        $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật thông tin thành công'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
} 