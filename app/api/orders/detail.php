<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Kiểm tra order_id
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $orderId = (int)$_GET['id'];
    
    // Lấy thông tin đơn hàng
    $stmt = $conn->prepare("
        SELECT o.*, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Lấy chi tiết đơn hàng với thông tin sản phẩm
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} 