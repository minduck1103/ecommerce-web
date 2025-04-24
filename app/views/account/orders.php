<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /shoppingcart/login');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách đơn hàng
try {
    $sql = "SELECT o.*, COUNT(oi.id) as total_items 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Có lỗi xảy ra khi tải danh sách đơn hàng";
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(146, 155, 161);
            --border-color: #e5e7eb;
            --background-color: #f8f9fa;
            --text-color: #2d3748;
            --text-muted: #718096;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-color);
        }

        .order-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .order-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .order-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-weight: 600;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .order-date {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .order-body {
            padding: 1.5rem;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-value {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            width: fit-content;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-processing {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .status-shipped {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-delivered {
            background-color: #f0fdf4;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .order-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .btn-details {
            background: linear-gradient(135deg, var(--primary-color), rgb(169, 177, 183));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-details:hover {
            background: linear-gradient(135deg, rgb(83, 158, 163), var(--primary-color));
            transform: translateY(-2px);
            color: white;
        }

        /* Modal Styles */
        .order-modal .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        .order-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), rgb(169, 177, 183));
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 1.5rem;
        }

        .order-modal .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .order-modal .modal-body {
            padding: 2rem;
        }

        .order-items {
            margin-top: 1.5rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .item-price {
            color: var(--primary-color);
            font-weight: 700;
        }

        .item-quantity {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .modal-summary {
            background-color: var(--background-color);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .summary-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .summary-value {
            color: var(--text-color);
            font-weight: 600;
        }

        .total-row {
            border-top: 2px dashed var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .total-row .summary-label,
        .total-row .summary-value {
            font-size: 1.25rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .order-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .order-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .btn-details {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="orders-container">
        <div class="page-header">
            <h1 class="page-title">Đơn hàng của tôi</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                    <h3>Bạn chưa có đơn hàng nào</h3>
                    <p class="text-muted">Hãy mua sắm để trải nghiệm dịch vụ của chúng tôi</p>
                    <a href="/shoppingcart/products" class="btn btn-primary mt-3">Mua sắm ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Đơn hàng #<?php echo $order['id']; ?></div>
                                <div class="order-date">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                            <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                <?php
                                    $status_text = [
                                        'pending' => 'Chờ xử lý',
                                        'processing' => 'Đang xử lý',
                                        'shipped' => 'Đang giao hàng',
                                        'delivered' => 'Đã giao hàng',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $status_text[$order['status']] ?? $order['status'];
                                ?>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="order-info">
                                <div class="info-group">
                                    <div class="info-label">Người nhận</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Số điện thoại</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Số lượng sản phẩm</div>
                                    <div class="info-value"><?php echo $order['total_items']; ?> sản phẩm</div>
                                </div>
                            </div>
                        </div>
                        <div class="order-footer">
                            <div class="order-total">
                                <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫
                            </div>
                            <button class="btn btn-details" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Modal Chi tiết đơn hàng -->
    <div class="modal fade order-modal" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng #<span id="modalOrderId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const orderDetailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));

        async function showOrderDetails(orderId) {
            document.getElementById('modalOrderId').textContent = orderId;
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            `;
            
            orderDetailModal.show();

            try {
                const response = await fetch(`/shoppingcart/api/orders/detail.php?id=${orderId}`);
                const data = await response.json();

                if (data.success) {
                    const order = data.order;
                    const items = data.items;

                    let itemsHtml = '';
                    items.forEach(item => {
                        itemsHtml += `
                            <div class="order-item">
                                <img src="/shoppingcart/public/uploads/products/${item.image}" 
                                     alt="${item.name}" 
                                     class="item-image"
                                     onerror="this.src='/shoppingcart/public/images/no-image.jpg'">
                                <div class="item-details">
                                    <div class="item-name">${item.name}</div>
                                    <div class="item-price">${new Intl.NumberFormat('vi-VN').format(item.price)}₫</div>
                                    <div class="item-quantity">Số lượng: ${item.quantity}</div>
                                </div>
                            </div>
                        `;
                    });

                    document.getElementById('modalContent').innerHTML = `
                        <div class="order-info mb-4">
                            <div class="info-group">
                                <div class="info-label">Người nhận</div>
                                <div class="info-value">${order.full_name}</div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Số điện thoại</div>
                                <div class="info-value">${order.phone}</div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Địa chỉ</div>
                                <div class="info-value">${order.address}</div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value">${order.email}</div>
                            </div>
                        </div>

                        <h6 class="mb-3">Sản phẩm đã mua</h6>
                        <div class="order-items">
                            ${itemsHtml}
                        </div>

                        <div class="modal-summary">
                            <div class="summary-row">
                                <span class="summary-label">Tạm tính</span>
                                <span class="summary-value">${new Intl.NumberFormat('vi-VN').format(order.total_amount - order.shipping_fee)}₫</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Phí vận chuyển</span>
                                <span class="summary-value">${new Intl.NumberFormat('vi-VN').format(order.shipping_fee)}₫</span>
                            </div>
                            <div class="summary-row total-row">
                                <span class="summary-label">Tổng cộng</span>
                                <span class="summary-value">${new Intl.NumberFormat('vi-VN').format(order.total_amount)}₫</span>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('modalContent').innerHTML = `
                        <div class="alert alert-danger">
                            ${data.message || 'Có lỗi xảy ra khi tải thông tin đơn hàng'}
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('modalContent').innerHTML = `
                    <div class="alert alert-danger">
                        Có lỗi xảy ra khi tải thông tin đơn hàng
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 