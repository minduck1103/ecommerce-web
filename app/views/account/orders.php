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
    $stmt = $conn->prepare("
        SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items,
        (SELECT SUM(quantity * price) FROM order_items WHERE order_id = o.id) as total_amount
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
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
    <title>Đơn hàng của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .order-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: #fff;
        }
        .order-header {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        .order-body {
            padding: 20px;
        }
        .order-footer {
            padding: 15px 20px;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
        .info-group {
            margin-bottom: 1rem;
        }
        .info-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        .info-group p {
            margin-bottom: 0;
            color: #212529;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Đơn hàng của tôi</h2>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="bi bi-bag-x" style="font-size: 3rem; color: #6c757d;"></i>
                <h4 class="mt-3">Bạn chưa có đơn hàng nào</h4>
                <p class="text-muted">Hãy khám phá các sản phẩm của chúng tôi</p>
                <a href="/shoppingcart/products" class="btn btn-primary mt-2">
                    <i class="bi bi-cart-plus me-2"></i>Mua sắm ngay
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">Đơn hàng #<?php echo $order['id']; ?></h5>
                                <p class="mb-0 text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php
                                    echo match($order['status']) {
                                        'pending' => 'Chờ xử lý',
                                        'processing' => 'Đang xử lý',
                                        'shipped' => 'Đã giao cho vận chuyển',
                                        'delivered' => 'Đã giao hàng',
                                        'cancelled' => 'Đã hủy',
                                        default => 'Không xác định'
                                    };
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Người nhận</label>
                                    <p><?php echo htmlspecialchars($order['full_name']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Số điện thoại</label>
                                    <p><?php echo htmlspecialchars($order['phone']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Địa chỉ giao hàng</label>
                                    <p><?php echo htmlspecialchars($order['address']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Ghi chú</label>
                                    <p><?php echo $order['note'] ? htmlspecialchars($order['note']) : 'Không có'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <span class="text-muted">Số lượng sản phẩm:</span>
                                    <strong><?php echo $order['total_items']; ?></strong>
                                </p>
                                <p class="mb-0">
                                    <span class="text-muted">Tổng tiền:</span>
                                    <strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</strong>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <button type="button" 
                                        class="btn btn-primary btn-sm"
                                        onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="bi bi-eye me-1"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Modal Chi tiết đơn hàng -->
            <div class="modal fade" id="orderDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Chi tiết đơn hàng #<span id="orderIdSpan"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="orderDetails">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Đang tải...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));

        function viewOrderDetails(orderId) {
            const orderDetails = document.getElementById('orderDetails');
            orderDetails.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            `;
            
            document.getElementById('orderIdSpan').textContent = orderId;
            orderDetailsModal.show();

            fetch(`/shoppingcart/api/orders/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const items = data.orderDetails;

                        let html = `
                            <div class="table-responsive mb-4">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Đơn giá</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        items.forEach(item => {
                            html += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="/shoppingcart/public/images/${item.product_image}" 
                                                 alt="${item.product_name}"
                                                 class="product-image me-3">
                                            <span>${item.product_name}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.price)}đ</td>
                                    <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)}đ</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                            <td class="text-end"><strong>${new Intl.NumberFormat('vi-VN').format(order.total_amount)}đ</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label>Trạng thái đơn hàng</label>
                                        <p>
                                            <span class="status-badge status-${order.status.toLowerCase()}">
                                                ${getStatusText(order.status)}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="info-group">
                                        <label>Ngày đặt hàng</label>
                                        <p>${new Date(order.created_at).toLocaleString('vi-VN')}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <label>Địa chỉ giao hàng</label>
                                        <p>${order.address}</p>
                                    </div>
                                    <div class="info-group">
                                        <label>Ghi chú</label>
                                        <p>${order.note || 'Không có'}</p>
                                    </div>
                                </div>
                            </div>
                        `;

                        orderDetails.innerHTML = html;
                    } else {
                        orderDetails.innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Không thể tải thông tin đơn hàng
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    orderDetails.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Có lỗi xảy ra khi tải thông tin đơn hàng
                        </div>
                    `;
                });
        }

        function getStatusText(status) {
            const statusMap = {
                'pending': 'Chờ xử lý',
                'processing': 'Đang xử lý',
                'shipped': 'Đã giao cho vận chuyển',
                'delivered': 'Đã giao hàng',
                'cancelled': 'Đã hủy'
            };
            return statusMap[status] || 'Không xác định';
        }
    </script>
</body>
</html> 