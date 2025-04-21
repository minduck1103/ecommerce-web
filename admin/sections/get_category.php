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

    // Kiểm tra ID danh mục
    if (!isset($_GET['id'])) {
        throw new Exception('ID danh mục không được cung cấp');
    }

    $categoryId = intval($_GET['id']);
    
    // Lấy thông tin danh mục
    $stmt = $conn->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        throw new Exception('Không tìm thấy danh mục');
    }

    echo json_encode([
        'success' => true,
        'category' => $category
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 