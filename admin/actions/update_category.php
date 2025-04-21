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
    if (!isset($_POST['id']) || !isset($_POST['name'])) {
        throw new Exception('Thiếu thông tin cần thiết');
    }

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate dữ liệu
    if (empty($name)) {
        throw new Exception('Tên danh mục không được để trống');
    }

    // Kiểm tra tên danh mục đã tồn tại chưa (trừ danh mục hiện tại)
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Tên danh mục đã tồn tại');
    }

    // Cập nhật danh mục
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    if (!$stmt->execute([$name, $description, $id])) {
        throw new Exception('Không thể cập nhật danh mục');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật danh mục thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 