<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
    exit;
}

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'];
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

try {
    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = ?, 
            notes = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$status, $notes, $order_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể cập nhật trạng thái'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
?> 