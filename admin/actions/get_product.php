<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    checkAdminAuth();
    
    if (!isset($_GET['id'])) {
        throw new Exception('ID sản phẩm không được cung cấp');
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }

    echo json_encode([
        'success' => true,
        'product' => $product
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 