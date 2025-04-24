<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$conn = $database->getConnection();

// Get order ID from query parameter
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $orderId) {
    try {
        // Get order details
        $stmt = $conn->prepare("
            SELECT o.*, u.full_name, u.phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Get order items
        $stmt = $conn->prepare("
            SELECT od.*, p.name as product_name, p.image as product_image
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'order' => $order,
            'orderDetails' => $orderDetails
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed or invalid order ID'
    ]);
} 