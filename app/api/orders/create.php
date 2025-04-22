<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng']);
    exit;
}

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    
    // Validate dữ liệu
    $required_fields = ['fullname', 'phone', 'email', 'address'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception('Vui lòng điền đầy đủ thông tin');
        }
    }
    
    // Bắt đầu transaction
    $conn->beginTransaction();
    
    // Tạo đơn hàng mới
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, fullname, phone, email, address, total_amount, shipping_fee, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    // Tính tổng tiền và phí ship
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_price->execute([$product_id]);
        $product = $stmt_price->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $total_amount += $product['price'] * $item['quantity'];
        }
    }
    
    $shipping_fee = $total_amount >= 1000000 ? 0 : 30000;
    $final_total = $total_amount + $shipping_fee;
    
    $stmt->execute([
        $_SESSION['user_id'],
        $data['fullname'],
        $data['phone'],
        $data['email'],
        $data['address'],
        $total_amount,
        $shipping_fee
    ]);
    
    $order_id = $conn->lastInsertId();
    
    // Thêm chi tiết đơn hàng
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_price->execute([$product_id]);
        $product = $stmt_price->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $stmt->execute([
                $order_id,
                $product_id,
                $item['quantity'],
                $product['price']
            ]);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Lưu order_id vào session
    $_SESSION['last_order_id'] = $order_id;
    
    // Xóa giỏ hàng
    unset($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đặt hàng thành công',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 