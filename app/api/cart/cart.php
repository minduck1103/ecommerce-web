<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Nhận dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);
    $action = isset($data['action']) ? $data['action'] : '';

    switch ($action) {
        case 'add':
            $product_id = $data['product_id'] ?? 0;
            if ($product_id > 0) {
                // Kiểm tra sản phẩm có tồn tại không
                $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    // Thêm hoặc cập nhật sản phẩm trong giỏ hàng
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product_id,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => 1
                    ];

                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                        'cart_count' => count($_SESSION['cart'])
                    ]);
                    return;
                }
            }
            throw new Exception('Sản phẩm không tồn tại');
            break;

        case 'remove':
            $product_id = $data['product_id'] ?? 0;
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
                    'cart_count' => count($_SESSION['cart'])
                ]);
                return;
            }
            throw new Exception('Sản phẩm không tồn tại trong giỏ hàng');
            break;

        case 'update':
            $product_id = $data['product_id'] ?? 0;
            $quantity = $data['quantity'] ?? 0;
            if (isset($_SESSION['cart'][$product_id]) && $quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã cập nhật số lượng',
                    'cart_count' => count($_SESSION['cart'])
                ]);
                return;
            }
            throw new Exception('Không thể cập nhật số lượng');
            break;

        case 'count':
            echo json_encode([
                'success' => true,
                'cart_count' => count($_SESSION['cart'])
            ]);
            return;
            break;

        default:
            throw new Exception('Hành động không hợp lệ');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 