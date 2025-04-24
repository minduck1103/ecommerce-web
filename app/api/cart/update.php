<?php
session_start();
header('Content-Type: application/json');

// Nhận dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id']) || !isset($data['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin sản phẩm hoặc số lượng'
    ]);
    exit;
}

$product_id = $data['product_id'];
$quantity = intval($data['quantity']);

// Kiểm tra giới hạn số lượng
if ($quantity < 1 || $quantity > 99) {
    echo json_encode([
        'success' => false,
        'message' => 'Số lượng không hợp lệ'
    ]);
    exit;
}

// Kiểm tra sản phẩm có tồn tại trong giỏ hàng không
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm không tồn tại trong giỏ hàng'
    ]);
    exit;
}

// Cập nhật số lượng
$_SESSION['cart'][$product_id]['quantity'] = $quantity;

// Tính toán lại tổng số lượng trong giỏ hàng
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Cập nhật số lượng thành công',
    'cart_count' => $total_items
]); 