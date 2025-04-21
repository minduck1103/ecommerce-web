<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    // Kiểm tra dữ liệu gửi lên
    if (empty($_POST['name'])) {
        throw new Exception('Vui lòng nhập tên danh mục');
    }

    // Kiểm tra danh mục đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$_POST['name']]);
    if ($stmt->fetch()) {
        throw new Exception('Danh mục này đã tồn tại');
    }

    // Thêm danh mục mới
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'] ?? ''
    ]);

    exit(json_encode([
        'success' => true,
        'message' => 'Thêm danh mục thành công',
        'category_id' => $conn->lastInsertId()
    ]));

} catch (Exception $e) {
    exit(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}
?> 