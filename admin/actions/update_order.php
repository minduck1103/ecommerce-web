<?php
require_once '../../app/config/config.php';
require_once '../auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }

    $orderId = $data['order_id'];
    $status = $data['status'];
    $note = $data['note'] ?? '';

    // Validate status
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $orderId]);

    // Add note if provided
    if (!empty($note)) {
        $stmt = $conn->prepare("UPDATE orders SET note = ? WHERE id = ?");
        $stmt->execute([$note, $orderId]);
    }

    // Get updated order details
    $stmt = $conn->prepare("
        SELECT o.*, u.email as customer_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Send email notification to customer
    if ($order) {
        $to = $order['customer_email'];
        $subject = "Cập nhật đơn hàng #{$orderId}";
        $message = "
            <h2>Cập nhật trạng thái đơn hàng</h2>
            <p>Đơn hàng #{$orderId} của bạn đã được cập nhật sang trạng thái: <strong>{$status}</strong></p>
        ";
        if (!empty($note)) {
            $message .= "<p>Ghi chú: {$note}</p>";
        }
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: ShopCart <noreply@shopcart.com>\r\n";
        
        mail($to, $subject, $message, $headers);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Order status updated successfully',
        'order' => $order
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 