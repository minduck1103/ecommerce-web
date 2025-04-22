<?php
session_start();
require_once '../includes/config.php';
require_once '../../app/config/database.php';

// Kiểm tra xác thực admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Khởi tạo kết nối database
try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

header('Content-Type: application/json');

// Xử lý GET request - Lấy chi tiết đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $orderId = (int)$_GET['id'];
        if ($orderId <= 0) {
            throw new Exception('Invalid order ID');
        }

        // Lấy thông tin đơn hàng
        $stmt = $conn->prepare("
            SELECT o.*, u.username, u.email, u.full_name, u.phone, u.address 
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Lấy chi tiết sản phẩm trong đơn hàng
        $stmt = $conn->prepare("
            SELECT od.*, p.name as product_name, p.image as product_image, p.price
            FROM order_details od
            LEFT JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'order' => $order,
            'orderDetails' => $orderDetails
        ]);

    } catch (Exception $e) {
        error_log("Get order error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý PUT request - Cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id']) || !isset($data['status'])) {
            throw new Exception('Missing required fields');
        }

        $orderId = (int)$data['id'];
        $status = $data['status'];

        // Validate status
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status');
        }

        // Kiểm tra đơn hàng tồn tại
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        if (!$stmt->fetch()) {
            throw new Exception('Order not found');
        }

        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);

    } catch (Exception $e) {
        error_log("Update order status error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý DELETE request - Xóa đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            throw new Exception('Missing order ID');
        }

        $orderId = (int)$data['id'];
        if ($orderId <= 0) {
            throw new Exception('Invalid order ID');
        }

        // Kiểm tra đơn hàng tồn tại
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        if (!$stmt->fetch()) {
            throw new Exception('Order not found');
        }

        // Bắt đầu transaction
        $conn->beginTransaction();

        // Xóa chi tiết đơn hàng
        $stmt = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->execute([$orderId]);

        // Xóa đơn hàng
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Delete order error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Method không được hỗ trợ
echo json_encode([
    'success' => false,
    'message' => 'Method not supported'
]);
exit; 