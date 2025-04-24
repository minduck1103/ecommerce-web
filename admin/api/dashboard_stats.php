<?php
require_once '../config.php';
require_once '../../app/config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Lấy tổng số đơn hàng
    $stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy số đơn hàng theo từng trạng thái
    $stmt = $conn->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM orders
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Khởi tạo mảng kết quả với giá trị mặc định là 0
    $stats = [
        'total_orders' => $total,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,
        'delivered_orders' => 0,
        'cancelled_orders' => 0
    ];

    // Cập nhật số lượng cho từng trạng thái
    foreach ($statusCounts as $status) {
        switch ($status['status']) {
            case 'pending':
                $stats['pending_orders'] = $status['count'];
                break;
            case 'processing':
                $stats['processing_orders'] = $status['count'];
                break;
            case 'shipped':
                $stats['shipped_orders'] = $status['count'];
                break;
            case 'delivered':
                $stats['delivered_orders'] = $status['count'];
                break;
            case 'cancelled':
                $stats['cancelled_orders'] = $status['count'];
                break;
        }
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
    ]);
} 