<?php
require_once '../config.php';
require_once '../../app/config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách đơn hàng
try {
    $stmt = $conn->query("
        SELECT o.*, u.username, u.email 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}

// Hàm lấy màu cho trạng thái
function getStatusColor($status) {
    return match($status) {
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}

// Hàm lấy text cho trạng thái
function getStatusText($status) {
    return match($status) {
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'shipped' => 'Đã giao cho vận chuyển',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định'
    };
}
?>

<!-- CSS styles -->
<style>
.action-column {
    text-align: center !important;
    vertical-align: middle !important;
    width: 100px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    margin: 0 3px;
}

/* Custom Dialog Styles */
.custom-dialog-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
}

.custom-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    z-index: 1051;
    min-width: 320px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.custom-dialog-message {
    margin-bottom: 20px;
    font-size: 16px;
    text-align: center;
}

.custom-dialog-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* Order Details Modal Styles */
.order-details-modal {
    max-width: 800px !important;
}

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.order-status {
    font-weight: bold;
}

.status-pending { color: #ffc107; }
.status-processing { color: #17a2b8; }
.status-completed { color: #28a745; }
.status-cancelled { color: #dc3545; }

/* Toast Styles */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1060;
}

.success-toast {
    background-color: #198754;
    color: white;
}
</style>

<!-- Bảng danh sách đơn hàng -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Quản lý đơn hàng</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-shopping-cart me-1"></i>
            Danh sách đơn hàng
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ordersTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th class="action-column">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</td>
                            <td>
                                <select class="form-select form-select-sm status-select" 
                                        onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)"
                                        style="width: auto; display: inline-block;">
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Đã giao cho vận chuyển</option>
                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Đã giao hàng</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td class="action-column">
                                <button type="button" class="btn btn-sm btn-info btn-action" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-action" onclick="confirmDelete(<?= $order['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg order-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng #<span id="orderIdSpan"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Thông tin khách hàng</h6>
                        <p><strong>Họ tên:</strong> <span id="customerName"></span></p>
                        <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                        <p><strong>Số điện thoại:</strong> <span id="customerPhone"></span></p>
                        <p><strong>Địa chỉ:</strong> <span id="customerAddress"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Thông tin đơn hàng</h6>
                        <p><strong>Mã đơn hàng:</strong> #<span id="orderId"></span></p>
                        <p><strong>Ngày đặt:</strong> <span id="orderDate"></span></p>
                        <p><strong>Trạng thái:</strong> <span id="orderStatus"></span></p>
                        <p><strong>Tổng tiền:</strong> <span id="orderTotal"></span></p>
                    </div>
                </div>
                <h6 class="mb-3">Chi tiết sản phẩm</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 60px">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th style="width: 100px">Số lượng</th>
                                <th style="width: 120px">Đơn giá</th>
                                <th style="width: 120px">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="orderProducts"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Dialog -->
<div class="custom-dialog-overlay" id="deleteConfirmDialog">
    <div class="custom-dialog">
        <div class="custom-dialog-message">
            Bạn có chắc chắn muốn xóa đơn hàng này?
        </div>
        <div class="custom-dialog-buttons">
            <button type="button" class="btn btn-primary" onclick="handleDeleteConfirm()">Có</button>
            <button type="button" class="btn btn-secondary" onclick="hideDeleteDialog()">Không</button>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="toast-container">
    <div class="toast success-toast" id="successToast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body" id="toastMessage">
            Thao tác thành công
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo DataTable
    $('#ordersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[5, 'desc']] // Sắp xếp theo ngày đặt hàng
    });

    // Khởi tạo toast
    const successToast = new bootstrap.Toast(document.getElementById('successToast'), {
        delay: 3000
    });
    window.successToast = successToast;

    // Khởi tạo modal
    window.orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
});

function viewOrderDetails(id) {
    fetch(`../api/orders.php?id=${id}`)
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                if (text.includes('<!DOCTYPE') || text.includes('Unauthorized')) {
                    window.location.href = '../login.php';
                    throw new Error('Phiên làm việc đã hết hạn');
                }
                throw new Error('Invalid response format');
            }
        })
        .then(data => {
            if (data.success) {
                const order = data.order;
                const details = data.orderDetails;

                // Cập nhật thông tin khách hàng
                document.getElementById('customerName').textContent = order.full_name || 'N/A';
                document.getElementById('customerEmail').textContent = order.email || 'N/A';
                document.getElementById('customerPhone').textContent = order.phone || 'N/A';
                document.getElementById('customerAddress').textContent = order.address || 'N/A';

                // Cập nhật thông tin đơn hàng
                document.getElementById('orderIdSpan').textContent = order.id;
                document.getElementById('orderId').textContent = order.id;
                document.getElementById('orderDate').textContent = new Date(order.created_at).toLocaleString('vi-VN');
                document.getElementById('orderStatus').textContent = getStatusText(order.status);
                document.getElementById('orderTotal').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(order.total_amount);

                // Cập nhật danh sách sản phẩm
                const productsHtml = details.map(item => `
                    <tr>
                        <td><img src="/shoppingcart/public/images/${item.product_image}" class="product-image" alt="${item.product_name}"></td>
                        <td>${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price)}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price * item.quantity)}</td>
                    </tr>
                `).join('');
                document.getElementById('orderProducts').innerHTML = productsHtml;

                // Hiển thị modal
                orderDetailsModal.show();
            } else {
                throw new Error(data.message || 'Không thể tải thông tin đơn hàng');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (error.message === 'Phiên làm việc đã hết hạn') {
                alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
                window.location.href = '../login.php';
            } else {
                alert(error.message || 'Có lỗi xảy ra khi tải thông tin đơn hàng');
            }
        });
}

function confirmDelete(id) {
    if (!id) {
        console.error('No order ID provided');
        return;
    }

    document.getElementById('deleteConfirmDialog').style.display = 'block';
    window.orderIdToDelete = id;
}

function hideDeleteDialog() {
    document.getElementById('deleteConfirmDialog').style.display = 'none';
    window.orderIdToDelete = null;
}

function handleDeleteConfirm() {
    const id = window.orderIdToDelete;
    if (!id) {
        console.error('No order ID to delete');
        return;
    }
    
    deleteOrder(id);
    hideDeleteDialog();
}

function deleteOrder(id) {
    fetch('../api/orders.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id }),
        credentials: 'include'
    })
    .then(response => response.text())
    .then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Response text:', text);
            if (text.includes('<!DOCTYPE') || text.includes('Unauthorized')) {
                window.location.href = '../login.php';
                throw new Error('Phiên làm việc đã hết hạn');
            }
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        if (data.success) {
            // Hiển thị toast thành công
            window.successToast.show();
            
            // Đợi 1 giây trước khi reload
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi xóa đơn hàng');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        if (error.message === 'Phiên làm việc đã hết hạn') {
            alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            window.location.href = '../login.php';
        } else {
            alert(error.message || 'Có lỗi xảy ra khi xóa đơn hàng. Vui lòng thử lại sau.');
        }
    });
}

function updateOrderStatus(id, status) {
    if (!id || !status) {
        console.error('Missing required fields');
        return;
    }

    fetch('../api/orders.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id, status: status }),
        credentials: 'include'
    })
    .then(response => response.text())
    .then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Response text:', text);
            if (text.includes('<!DOCTYPE') || text.includes('Unauthorized')) {
                window.location.href = '../login.php';
                throw new Error('Phiên làm việc đã hết hạn');
            }
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        if (data.success) {
            // Cập nhật thông báo toast
            document.getElementById('toastMessage').textContent = 'Cập nhật trạng thái thành công';
            window.successToast.show();
            
            // Cập nhật màu sắc của status
            const statusCell = event.target.closest('td');
            const statusClass = `status-${status.toLowerCase()}`;
            statusCell.className = '';
            statusCell.classList.add(statusClass);
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        if (error.message === 'Phiên làm việc đã hết hạn') {
            alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            window.location.href = '../login.php';
        } else {
            alert(error.message || 'Có lỗi xảy ra khi cập nhật trạng thái. Vui lòng thử lại sau.');
        }
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