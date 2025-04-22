<?php
require_once __DIR__ . '/../config/database.php';
session_start();

header('Content-Type: application/json');

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu từ request body
$data = json_decode(file_get_contents('php://input'), true);

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý xóa sản phẩm
if (isset($data['action']) && $data['action'] === 'remove') {
    if (!isset($data['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        exit;
    }

    $productId = $data['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $cartCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
        }
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    exit;
}

// Xử lý thêm/cập nhật sản phẩm
if (!isset($data['product_id']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID or quantity']);
    exit;
}

$productId = $data['product_id'];
$quantity = (int)$data['quantity'];

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Kiểm tra sản phẩm có tồn tại không
    $stmt = $conn->prepare("SELECT id, name, price, quantity as stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Kiểm tra số lượng tồn kho
    if ($quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }

    // Thêm hoặc cập nhật sản phẩm trong giỏ hàng
    $_SESSION['cart'][$productId] = [
        'id' => $productId,
        'quantity' => $quantity,
        'price' => $product['price']
    ];

    // Tính tổng số lượng sản phẩm trong giỏ hàng
    $cartCount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true, 
        'cart_count' => $cartCount,
        'message' => 'Product added to cart successfully'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 