<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../../app/config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Hàm chuyển đổi trạng thái sang tiếng Việt
function getStatusText($status) {
    switch($status) {
        case 'pending': return 'Chờ xử lý';
        case 'processing': return 'Đang xử lý';
        case 'shipped': return 'Đã giao cho vận chuyển';
        case 'delivered': return 'Đã giao hàng';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}

// Get statistics
try {
    // Lấy thống kê cơ bản
    $stats = [
        'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'revenue' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'delivered'")->fetchColumn()
    ];

    // Lấy số lượng đơn hàng theo trạng thái
    $orderStats = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Khởi tạo mảng đếm theo trạng thái
    $stats['pending_orders'] = 0;
    $stats['processing_orders'] = 0;
    $stats['shipped_orders'] = 0;
    $stats['delivered_orders'] = 0;
    $stats['cancelled_orders'] = 0;

    // Cập nhật số lượng cho từng trạng thái
    foreach ($orderStats as $stat) {
        switch ($stat['status']) {
            case 'pending':
                $stats['pending_orders'] = $stat['count'];
                break;
            case 'processing':
                $stats['processing_orders'] = $stat['count'];
                break;
            case 'shipped':
                $stats['shipped_orders'] = $stat['count'];
                break;
            case 'delivered':
                $stats['delivered_orders'] = $stat['count'];
                break;
            case 'cancelled':
                $stats['cancelled_orders'] = $stat['count'];
                break;
        }
    }

} catch (PDOException $e) {
    $stats = [
        'products' => 0,
        'categories' => 0,
        'users' => 0,
        'orders' => 0,
        'revenue' => 0,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,
        'delivered_orders' => 0,
        'cancelled_orders' => 0
    ];
}
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col">
            <div class="stat-card bg-primary">
                <i class="fas fa-box"></i>
                <h3>Sản phẩm</h3>
                <p class="h4"><?php echo isset($stats['products']) ? $stats['products'] : 'N/A'; ?></p>
            </div>
        </div>
        <div class="col">
            <div class="stat-card bg-success">
                <i class="fas fa-tags"></i>
                <h3>Danh mục</h3>
                <p class="h4"><?php echo isset($stats['categories']) ? $stats['categories'] : 'N/A'; ?></p>
            </div>
        </div>
        <div class="col">
            <div class="stat-card bg-info">
                <i class="fas fa-users"></i>
                <h3>Người dùng</h3>
                <p class="h4"><?php echo isset($stats['users']) ? $stats['users'] : 'N/A'; ?></p>
            </div>
        </div>
        <div class="col">
            <div class="stat-card bg-warning">
                <i class="fas fa-shopping-cart"></i>
                <h3>Đơn hàng</h3>
                <p class="h4"><?php echo isset($stats['orders']) ? $stats['orders'] : 'N/A'; ?></p>
            </div>
        </div>
        <div class="col">
            <div class="stat-card bg-danger">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Doanh thu</h3>
                <p class="h4"><?php echo number_format($stats['revenue'], 0, ',', '.'); ?>đ</p>
            </div>
        </div>
    </div>

    <!-- Order Status Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thống kê trạng thái đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Chờ xử lý</h6>
                                    <p class="card-text h4" id="pendingOrders"><?php echo $stats['pending_orders']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Đang xử lý</h6>
                                    <p class="card-text h4" id="processingOrders"><?php echo $stats['processing_orders']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Đã giao cho vận chuyển</h6>
                                    <p class="card-text h4" id="shippedOrders"><?php echo $stats['shipped_orders']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Đã giao hàng</h6>
                                    <p class="card-text h4" id="deliveredOrders"><?php echo $stats['delivered_orders']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Đã hủy</h6>
                                    <p class="card-text h4" id="cancelledOrders"><?php echo $stats['cancelled_orders']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Đơn hàng gần đây</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                if (isset($conn)) {
                                    $query = "SELECT o.*, u.email as customer_email 
                                            FROM orders o 
                                            LEFT JOIN users u ON o.user_id = u.id 
                                            ORDER BY o.created_at DESC 
                                            LIMIT 5";
                                    $stmt = $conn->query($query);
                                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>{$order['id']}</td>";
                                        echo "<td>{$order['customer_email']}</td>";
                                        echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . "đ</td>";
                                        echo "<td>" . getStatusText($order['status']) . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>Không thể kết nối đến cơ sở dữ liệu</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5'>Lỗi: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 