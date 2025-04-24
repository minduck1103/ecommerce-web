<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Nhận và decode dữ liệu JSON
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Log request data for debugging
    error_log('Request data: ' . $jsonData);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate dữ liệu đầu vào
    if (empty($data['fullname']) || empty($data['phone']) || empty($data['email']) || empty($data['address'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        exit;
    }

    if (empty($data['shipping_method'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phương thức vận chuyển']);
        exit;
    }

    if (empty($data['payment_method'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phương thức thanh toán']);
        exit;
    }

    // Validate số điện thoại
    if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
        exit;
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        exit;
    }

    // Kiểm tra giỏ hàng
    if (empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }

    // Validate total_amount và shipping_fee
    if (!isset($data['total_amount']) || !is_numeric($data['total_amount'])) {
        echo json_encode(['success' => false, 'message' => 'Tổng tiền không hợp lệ']);
        exit;
    }

    if (!isset($data['shipping_fee']) || !is_numeric($data['shipping_fee'])) {
        echo json_encode(['success' => false, 'message' => 'Phí vận chuyển không hợp lệ']);
        exit;
    }
    
    try {
        // Debug session cart
        error_log('Cart contents: ' . print_r($_SESSION['cart'], true));

        // Kiểm tra và làm sạch giỏ hàng trước khi xử lý
        if (!empty($_SESSION['cart'])) {
            $productIds = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $sql = "SELECT id, price, name FROM products WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($productIds);
            $existingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to id => product array for easier access
            $existingProductsMap = array_column($existingProducts, null, 'id');
            
            // Lọc bỏ các sản phẩm không tồn tại khỏi giỏ hàng
            $invalidProducts = array_diff($productIds, array_keys($existingProductsMap));
            if (!empty($invalidProducts)) {
                foreach ($invalidProducts as $invalidId) {
                    unset($_SESSION['cart'][$invalidId]);
                }
            }

            // Kiểm tra xem giỏ hàng còn sản phẩm không
            if (empty($_SESSION['cart'])) {
                throw new Exception('Giỏ hàng không có sản phẩm hợp lệ');
            }

            // Tính lại tổng tiền dựa trên sản phẩm còn lại
            $total = 0;
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = $existingProductsMap[$productId];
                $total += $product['price'] * $item['quantity'];
            }

            // Cập nhật tổng tiền
            $data['total_amount'] = $total + $data['shipping_fee'];
        }

        // Bắt đầu transaction
        $conn->beginTransaction();

        try {
            // 1. Tạo đơn hàng mới
            $sql = "INSERT INTO orders (user_id, full_name, phone, email, address, shipping_method, payment_method, shipping_fee, total_amount, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $data['fullname'],
                $data['phone'],
                $data['email'],
                $data['address'],
                $data['shipping_method'],
                $data['payment_method'],
                $data['shipping_fee'],
                $data['total_amount']
            ]);

            if (!$result) {
                throw new Exception('Không thể tạo đơn hàng');
            }
            
            $orderId = $conn->lastInsertId();
            
            // 2. Thêm chi tiết đơn hàng với giá từ database
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = $existingProductsMap[$productId];
                $result = $stmt->execute([
                    $orderId,
                    $productId,
                    $item['quantity'],
                    $product['price']
                ]);

                if (!$result) {
                    throw new Exception('Không thể thêm chi tiết đơn hàng cho sản phẩm: ' . $product['name']);
                }
            }
            
            // 3. Xóa giỏ hàng
            unset($_SESSION['cart']);
            
            // Commit transaction
            $conn->commit();
            
            // Lưu order_id vào session để sử dụng ở trang success
            $_SESSION['last_order_id'] = $orderId;
            
            echo json_encode([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order_id' => $orderId
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } catch(Exception $e) {
        error_log('Order creation error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại.',
            'debug' => $e->getMessage()
        ]);
    }
} catch(Exception $e) {
    error_log('Order creation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại.',
        'debug' => $e->getMessage() // Chỉ để debug, nên xóa trong môi trường production
    ]);
}
?> 