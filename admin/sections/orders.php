<?php
require_once '../config.php';
require_once '../../app/config/database.php';

// Debug: Kiểm tra file được load
error_log("Loading orders.php");

// Khởi tạo kết nối database
try {
$database = new Database();
$conn = $database->getConnection();
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Không thể kết nối đến database");
}

// Lấy danh sách đơn hàng
try {
    $stmt = $conn->query("
        SELECT o.*, u.username, u.email 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found " . count($orders) . " orders");
} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage());
    $orders = [];
}

// Debug: In ra số lượng đơn hàng
echo "<!-- Debug: Found " . count($orders) . " orders -->";

// Hàm lấy màu cho trạng thái
function getStatusColor($status) {
    switch($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

// Hàm lấy text cho trạng thái
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

// Hàm lấy text cho phương thức vận chuyển
function getShippingMethodText($method) {
    switch($method) {
        case 'standard': return 'Giao hàng tiêu chuẩn';
        case 'express': return 'Giao hàng nhanh';
        case 'free': return 'Miễn phí vận chuyển';
        default: return $method;
    }
}

// Hàm lấy text cho phương thức thanh toán
function getPaymentMethodText($method) {
    switch($method) {
        case 'cod': return 'Thanh toán khi nhận hàng';
        case 'bank_transfer': return 'Chuyển khoản ngân hàng';
        case 'momo': return 'Ví MoMo';
        case 'vnpay': return 'VNPay';
        default: return $method;
    }
}
?>

<!-- CSS styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
                        <?php 
                        if (empty($orders)) {
                            echo '<tr><td colspan="7" class="text-center">Không có đơn hàng nào</td></tr>';
                        } else {
                            foreach ($orders as $order): 
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</td>
                            <td>
                                <select class="form-select form-select-sm status-select" 
                                        onchange="updateOrderStatus(<?= $order['id'] ?>, this.value, this)"
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
                                <button type="button" class="btn btn-sm btn-danger btn-action" onclick="showDeleteConfirm(<?= $order['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endforeach; 
                        }
                        ?>
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
                        <p><strong>Ngày đặt:</strong> <span id="orderDate"></span></p>
                        <p><strong>Trạng thái:</strong> <span id="orderStatus"></span></p>
                        <p><strong>Phương thức vận chuyển:</strong> <span id="shippingMethod"></span></p>
                        <p><strong>Phương thức thanh toán:</strong> <span id="paymentMethod"></span></p>
                        <p><strong>Phí vận chuyển:</strong> <span id="shippingFee"></span></p>
                        <p><strong>Tổng tiền:</strong> <span id="orderTotal"></span></p>
                        <p><strong>Ghi chú:</strong> <span id="orderNote"></span></p>
                    </div>
                </div>
                <h6 class="mb-3">Chi tiết sản phẩm</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Hình ảnh</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetailsTableBody">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tổng tiền sản phẩm:</strong></td>
                                <td id="subtotal"></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                <td id="shippingFeeTotal"></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td id="grandTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận xóa -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn hàng #<span id="deleteOrderId"></span>?</p>
                <p class="mb-0 text-danger">Lưu ý: Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    Xóa
                </button>
        </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container">
    <div class="toast" id="toastNotification" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body" id="toastMessage">
        </div>
    </div>
</div>

<script>
console.log('Script section started');

// JavaScript helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

function getStatusTextJS(status) {
    const statusMap = {
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipped': 'Đã giao cho vận chuyển',
        'delivered': 'Đã giao hàng',
        'cancelled': 'Đã hủy'
    };
    return statusMap[status] || 'Không xác định';
}

function getShippingMethodTextJS(method) {
    const methodMap = {
        'standard': 'Giao hàng tiêu chuẩn',
        'express': 'Giao hàng nhanh',
        'free': 'Miễn phí vận chuyển'
    };
    return methodMap[method] || method;
}

function getPaymentMethodTextJS(method) {
    const methodMap = {
        'cod': 'Thanh toán khi nhận hàng',
        'bank_transfer': 'Chuyển khoản ngân hàng',
        'momo': 'Ví MoMo',
        'vnpay': 'VNPay'
    };
    return methodMap[method] || method;
}

// Initialize DataTable when document is ready
$(document).ready(function() {
    console.log('Document ready');
    try {
        // Check jQuery
        console.log('jQuery version:', $.fn.jquery);
        
        // Check DataTables
        if ($.fn.DataTable) {
            console.log('DataTables is loaded');
            
            // Initialize DataTable
    $('#ordersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
                },
                "pageLength": 10,
                "order": [[5, 'desc']]
            });
            console.log('DataTable initialized');
        } else {
            console.error('DataTables is not loaded');
        }
    } catch (error) {
        console.error('Error in document ready:', error);
    }
});

// View order details
async function viewOrderDetails(orderId) {
    console.log('Viewing order details for ID:', orderId);
    try {
        const response = await fetch(`api/orders.php?id=${orderId}`);
        console.log('API Response:', response);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
                }
        
        const data = await response.json();
        console.log('Order data:', data);
        
            if (data.success) {
                const order = data.order;
            const orderDetails = data.orderDetails;

            // Update order information
            document.getElementById('orderIdSpan').textContent = order.id;
                document.getElementById('customerName').textContent = order.full_name || 'N/A';
                document.getElementById('customerEmail').textContent = order.email || 'N/A';
                document.getElementById('customerPhone').textContent = order.phone || 'N/A';
                document.getElementById('customerAddress').textContent = order.address || 'N/A';
            document.getElementById('orderDate').textContent = new Date(order.created_at).toLocaleString('vi-VN');
            document.getElementById('orderStatus').textContent = getStatusTextJS(order.status);
            document.getElementById('shippingMethod').textContent = getShippingMethodTextJS(order.shipping_method);
            document.getElementById('paymentMethod').textContent = getPaymentMethodTextJS(order.payment_method);
            document.getElementById('shippingFee').textContent = formatCurrency(order.shipping_fee);
            document.getElementById('orderTotal').textContent = formatCurrency(order.total_amount);
            document.getElementById('orderNote').textContent = order.note || 'Không có';
            
            // Update product details table
            const tbody = document.getElementById('orderDetailsTableBody');
            tbody.innerHTML = '';
            
            let subtotal = 0;
            
            orderDetails.forEach(detail => {
                const row = document.createElement('tr');
                const lineTotal = parseFloat(detail.price) * parseInt(detail.quantity);
                subtotal += lineTotal;
                
                const defaultImage = '/shoppingcart/public/images/no-image.jpg';
                const imageUrl = detail.product_image_url || defaultImage;
                
                row.innerHTML = `
                    <td>${detail.product_name || 'Sản phẩm không xác định'}</td>
                    <td>
                        <img src="${imageUrl}" 
                             class="product-image" 
                             alt="${detail.product_name || 'Sản phẩm'}"
                             onerror="this.src='${defaultImage}'">
                    </td>
                    <td>${formatCurrency(detail.price)}</td>
                    <td>${detail.quantity}</td>
                    <td>${formatCurrency(lineTotal)}</td>
                `;
                tbody.appendChild(row);
            });
            
            // Update totals
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('shippingFeeTotal').textContent = formatCurrency(order.shipping_fee);
            document.getElementById('grandTotal').textContent = formatCurrency(order.total_amount);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();
            } else {
            showToast('Lỗi', data.message || 'Không thể tải thông tin đơn hàng', 'error');
            }
    } catch (error) {
        console.error('Error in viewOrderDetails:', error);
        showToast('Lỗi', 'Có lỗi xảy ra khi tải thông tin đơn hàng', 'error');
            }
}

let orderIdToDelete = null;
const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

// Show delete confirmation modal
function showDeleteConfirm(orderId) {
    orderIdToDelete = orderId;
    document.getElementById('deleteOrderId').textContent = orderId;
    deleteConfirmModal.show();
}

// Handle order deletion
async function confirmDelete() {
    console.log('Confirming delete for order ID:', orderIdToDelete);
    if (!orderIdToDelete) return;

    try {
        const response = await fetch('api/orders.php', {
        method: 'DELETE',
        headers: {
                'Content-Type': 'application/json'
        },
            body: JSON.stringify({ id: orderIdToDelete })
        });

        const result = await response.json();

        if (result.success) {
            showToast('Thành công', 'Đã xóa đơn hàng thành công', 'success');
            deleteConfirmModal.hide();
            // Reload after 1 second
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Có lỗi xảy ra khi xóa đơn hàng');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi', error.message || 'Có lỗi xảy ra khi xóa đơn hàng', 'error');
    } finally {
        orderIdToDelete = null;
    }
}

// Show toast notification
function showToast(title, message, type = 'success') {
    console.log('Showing toast:', { title, message, type });
    const toast = document.getElementById('toastNotification');
    const toastMessage = document.getElementById('toastMessage');
    
    // Remove old classes
    toast.classList.remove('bg-success', 'bg-danger');
    
    // Add new class based on type
    toast.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
    
    toastMessage.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Update order status
function updateOrderStatus(id, status, element) {
    console.log('Updating order status:', { id, status });
    if (!id || !status) {
        console.error('Missing required fields');
        return;
    }

    fetch('api/orders.php', {
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
                window.location.href = 'login.php';
                throw new Error('Phiên làm việc đã hết hạn');
            }
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        if (data.success) {
            showToast('Thành công', 'Cập nhật trạng thái thành công');
            
            // Update status cell color
            const statusCell = element.closest('td');
            const statusClass = `status-${status.toLowerCase()}`;
            statusCell.className = '';
            statusCell.classList.add(statusClass);
            
            // Update dashboard statistics
            updateDashboardStats();
            
            // Reload page after 1 second
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        if (error.message === 'Phiên làm việc đã hết hạn') {
            alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            window.location.href = 'login.php';
        } else {
            showToast('Lỗi', error.message || 'Có lỗi xảy ra khi cập nhật trạng thái', 'error');
        }
    });
}

// Function to update dashboard statistics
function updateDashboardStats() {
    fetch('api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update order statistics in dashboard
                const stats = data.stats;
                if (window.parent.document.getElementById('totalOrders')) {
                    window.parent.document.getElementById('totalOrders').textContent = stats.total_orders || 0;
                }
                if (window.parent.document.getElementById('pendingOrders')) {
                    window.parent.document.getElementById('pendingOrders').textContent = stats.pending_orders || 0;
                }
                if (window.parent.document.getElementById('processingOrders')) {
                    window.parent.document.getElementById('processingOrders').textContent = stats.processing_orders || 0;
                }
                if (window.parent.document.getElementById('shippedOrders')) {
                    window.parent.document.getElementById('shippedOrders').textContent = stats.shipped_orders || 0;
                }
                if (window.parent.document.getElementById('deliveredOrders')) {
                    window.parent.document.getElementById('deliveredOrders').textContent = stats.delivered_orders || 0;
                }
                if (window.parent.document.getElementById('cancelledOrders')) {
                    window.parent.document.getElementById('cancelledOrders').textContent = stats.cancelled_orders || 0;
                }
            }
        })
        .catch(error => {
            console.error('Error updating dashboard stats:', error);
        });
}
</script> 

<?php
// Debug: Print end of file
error_log("End of orders.php");
?>