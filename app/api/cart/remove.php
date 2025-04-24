<?php
session_start();
header('Content-Type: application/json');

// Nhận dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin sản phẩm'
    ]);
    exit;
}

$product_id = $data['product_id'];

// Xóa sản phẩm khỏi giỏ hàng
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
    
    // Tính lại tổng số lượng sản phẩm trong giỏ hàng
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm không tồn tại trong giỏ hàng'
    ]);
} 