<?php
session_start();
require_once '../config.php';
require_once '../../app/config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Đặt header JSON
header('Content-Type: application/json');

// Xử lý các phương thức HTTP khác nhau
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Lấy chi tiết đơn hàng
        if (isset($_GET['id'])) {
            try {
                $orderId = (int)$_GET['id'];
                
                // Lấy thông tin đơn hàng
                $stmt = $conn->prepare("
                    SELECT o.*, u.username, u.email, u.full_name, u.phone, u.address,
                           o.shipping_method, o.payment_method, o.shipping_fee, o.note
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.id = ?
                ");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$order) {
                    throw new Exception('Không tìm thấy đơn hàng');
                }

                // Lấy chi tiết sản phẩm trong đơn hàng
                $stmt = $conn->prepare("
                    SELECT oi.*, p.name as product_name, p.image as product_image, 
                           CONCAT('/shoppingcart/public/uploads/products/', p.image) as product_image_url
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$orderId]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'order' => $order,
                    'orderDetails' => $orderItems
                ]);

            } catch (Exception $e) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
        }
        break;

    case 'PUT':
        // Xử lý cập nhật trạng thái đơn hàng
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['status'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }

        $orderId = filter_var($data['id'], FILTER_VALIDATE_INT);
        if ($orderId === false) {
            throw new Exception('ID đơn hàng không hợp lệ');
        }

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($data['status'], $validStatuses)) {
            throw new Exception('Trạng thái không hợp lệ');
        }

        // Kiểm tra đơn hàng tồn tại
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        if (!$stmt->fetch()) {
            throw new Exception('Không tìm thấy đơn hàng');
        }

        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $success = $stmt->execute([$data['status'], $orderId]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } else {
            throw new Exception('Không thể cập nhật trạng thái đơn hàng');
        }
        break;

    case 'DELETE':
        // Xử lý xóa đơn hàng
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            throw new Exception('Thiếu ID đơn hàng');
        }

        $orderId = filter_var($data['id'], FILTER_VALIDATE_INT);
        if ($orderId === false) {
            throw new Exception('ID đơn hàng không hợp lệ');
        }

        // Kiểm tra đơn hàng tồn tại
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        if (!$stmt->fetch()) {
            throw new Exception('Không tìm thấy đơn hàng');
        }

        // Xóa chi tiết đơn hàng trước
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);

        // Sau đó xóa đơn hàng
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $success = $stmt->execute([$orderId]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa đơn hàng thành công'
            ]);
        } else {
            throw new Exception('Không thể xóa đơn hàng');
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not supported']);
        break;
}

exit;