<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng'
    ]);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit;
}

// Lấy dữ liệu từ request
$product_id = $_POST['product_id'] ?? null;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate dữ liệu
if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin sản phẩm'
    ]);
    exit;
}

if ($quantity < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Số lượng không hợp lệ'
    ]);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Kiểm tra sản phẩm có tồn tại không
    $stmt = $conn->prepare("SELECT id, name, price, quantity as stock, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không tồn tại'
        ]);
        exit;
    }

    // Kiểm tra số lượng tồn kho
    if ($quantity > $product['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng sản phẩm trong kho không đủ'
        ]);
        exit;
    }

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm hoặc cập nhật sản phẩm trong giỏ hàng
    if (isset($_SESSION['cart'][$product_id])) {
        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Số lượng sản phẩm vượt quá số lượng trong kho'
            ]);
            exit;
        }
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
    }

    // Tính tổng số lượng sản phẩm trong giỏ hàng
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => $cart_count
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
} 