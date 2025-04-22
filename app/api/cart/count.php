<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $total_items = 0;
    
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total_items += $item['quantity'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'count' => $total_items
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi đếm số lượng sản phẩm',
        'count' => 0
    ]);
} 