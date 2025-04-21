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

    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID danh mục không được cung cấp');
    }

    $categoryId = intval($data['id']);

    // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        throw new Exception('Không thể xóa danh mục này vì có sản phẩm đang sử dụng');
    }

    // Xóa danh mục
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    if (!$stmt->execute([$categoryId])) {
        throw new Exception('Không thể xóa danh mục');
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception('Không tìm thấy danh mục');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Xóa danh mục thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 