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

// Get statistics
try {
    $stats = [
        'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'revenue' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats = [
        'products' => 0,
        'categories' => 0,
        'users' => 0,
        'orders' => 0,
        'revenue' => 0
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
                                        echo "<td>{$order['status']}</td>";
                                        echo "<td>{$order['created_at']}</td>";
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