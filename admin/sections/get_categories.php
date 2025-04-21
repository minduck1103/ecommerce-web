<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    // Lấy danh sách danh mục
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    exit(json_encode([
        'success' => true,
        'categories' => $categories
    ]));
} catch (Exception $e) {
    exit(json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách danh mục: ' . $e->getMessage()
    ]));
}
?> 