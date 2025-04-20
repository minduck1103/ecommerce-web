<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    checkAdminAuth();
    
    // Get and decode JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id'])) {
        throw new Exception('ID sản phẩm không được cung cấp');
    }

    // Get product info to delete image
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$data['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$data['id']]);

    // Delete product image if exists
    if ($product['image']) {
        $imagePath = '../../uploads/products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Xóa sản phẩm thành công'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 