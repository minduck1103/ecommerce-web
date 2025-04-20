<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT o.*, u.email 
                         FROM orders o 
                         JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll();
    
    $html = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý đơn hàng</h2>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>';

    foreach ($orders as $order) {
        $status_class = '';
        switch ($order['status']) {
            case 'pending':
                $status_class = 'bg-warning';
                $status_text = 'Chờ xử lý';
                break;
            case 'processing':
                $status_class = 'bg-info';
                $status_text = 'Đang xử lý';
                break;
            case 'completed':
                $status_class = 'bg-success';
                $status_text = 'Hoàn thành';
                break;
            case 'cancelled':
                $status_class = 'bg-danger';
                $status_text = 'Đã hủy';
                break;
            default:
                $status_class = 'bg-secondary';
                $status_text = $order['status'];
        }
            
        $html .= '
            <tr>
                <td>#' . $order['id'] . '</td>
                <td>' . htmlspecialchars($order['email']) . '</td>
                <td>' . number_format($order['total_amount'], 0, ',', '.') . 'đ</td>
                <td><span class="badge ' . $status_class . '">' . $status_text . '</span></td>
                <td>' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</td>
                <td>
                    <button class="btn btn-sm btn-info me-1" onclick="viewOrder(' . $order['id'] . ')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary me-1" onclick="updateOrderStatus(' . $order['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>';
    }

    $html .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tải dữ liệu đơn hàng'
    ]);
}
?> 