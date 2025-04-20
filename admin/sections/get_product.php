<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID sản phẩm không được cung cấp');
    }

    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }

    // Ensure numeric values are properly formatted
    $product['price'] = floatval($product['price']);
    $product['quantity'] = intval($product['quantity']);
    $product['status'] = intval($product['status']);

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