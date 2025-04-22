<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /shoppingcart/login?redirect=cart/checkout');
    exit;
}

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header('Location: /shoppingcart/cart');
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Lấy thông tin người dùng
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Lấy thông tin giỏ hàng
    $cart_items = [];
    $total = 0;
    
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        if (!empty($product_ids)) {
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            $sql = "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $productId = $product['id'];
                if (isset($_SESSION['cart'][$productId])) {
                    $quantity = $_SESSION['cart'][$productId]['quantity'];
                    $subtotal = $product['price'] * $quantity;
                    
                    $cart_items[] = [
                        'id' => $productId,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                    
                    $total += $subtotal;
                }
            }
        }
    }
    
    // Tính phí vận chuyển
    $shipping_fee = $total >= 1000000 ? 0 : 30000;
    $final_total = $total + $shipping_fee;
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error_message = "Đã có lỗi xảy ra. Vui lòng thử lại sau.";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color:rgb(146, 155, 161);
            --secondary-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --background-color: #f8f9fa;
            --border-color: #edf2f7;
            --text-color: #2d3748;
            --text-muted: #718096;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .checkout-header {
            background: linear-gradient(135deg, var(--primary-color),rgb(187, 193, 197));
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            color: white;
        }

        .checkout-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .checkout-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .checkout-section:hover {
            box-shadow: var(--shadow-md);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.75rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-color);
        }

        .section-title i {
            color: var(--primary-color);
            font-size: 1.35rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid var(--border-color);
            padding: 0.875rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .form-check {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }

        .form-check-input:checked ~ .form-check-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .order-summary {
            position: sticky;
            top: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            background-color: white;
            padding: 0.25rem;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .item-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0.25rem;
        }

        .item-quantity {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .summary {
            background-color: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .summary-label {
            font-weight: 600;
            color: var(--text-muted);
        }

        .summary-value {
            font-weight: 700;
            color: var(--text-color);
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
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color),rgb(169, 177, 183));
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 1.1rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg,rgb(83, 158, 163), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .shipping-note {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 300px;
            transform: translateX(0);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast.success {
            border-left: 4px solid var(--success-color);
        }

        .toast.error {
            border-left: 4px solid var(--secondary-color);
        }

        .toast i {
            font-size: 1.25rem;
        }

        .toast.success i {
            color: var(--success-color);
        }

        .toast.error i {
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .checkout-container {
                margin: 1rem;
            }

            .checkout-header {
                padding: 1.5rem;
                border-radius: 12px;
            }

            .checkout-section {
                padding: 1.5rem;
                border-radius: 12px;
            }

            .item-image {
                width: 70px;
                height: 70px;
            }

            .summary {
                padding: 1rem;
            }

            .btn-primary {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="toast-container"></div>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1 class="checkout-title">Thanh toán</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php else: ?>
        <div class="row">
                <div class="col-md-8">
                    <form id="checkoutForm" method="POST" action="/shoppingcart/api/orders/create" onsubmit="return handleSubmit(event)">
                        <div class="checkout-section">
                            <div class="section-title">
                                <i class="fas fa-user"></i>
                                Thông tin người nhận
                            </div>
                        <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="fullname">Họ và tên</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="phone">Số điện thoại</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="address">Địa chỉ nhận hàng</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="checkout-section">
                            <div class="section-title">
                                <i class="fas fa-truck"></i>
                                Phương thức vận chuyển
                        </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipping_method" id="standard_shipping" value="standard" checked>
                                <label class="form-check-label" for="standard_shipping">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-truck text-primary"></i>
                                        <div>
                                            <div class="fw-bold">Giao hàng tiêu chuẩn</div>
                                            <div class="text-muted small">Nhận hàng trong 2-3 ngày</div>
                        </div>
                        </div>
                                </label>
                            </div>
                        </div>

                        <div class="checkout-section">
                            <div class="section-title">
                                <i class="fas fa-money-bill"></i>
                                Phương thức thanh toán
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <div>
                                            <div class="fw-bold">Thanh toán khi nhận hàng (COD)</div>
                                            <div class="text-muted small">Thanh toán bằng tiền mặt khi nhận hàng</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="checkout-section order-summary">
                        <div class="section-title">
                            <i class="fas fa-shopping-cart"></i>
                            Đơn hàng của bạn
            </div>

                    <div class="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                                <img src="<?php echo htmlspecialchars('/shoppingcart/public/uploads/products/' . $item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="item-image"
                                     onerror="this.src='/shoppingcart/public/images/no-image.jpg'">
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</div>
                                    <div class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary">
                            <div class="summary-row">
                                <span class="summary-label">Tạm tính</span>
                                <span class="summary-value"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Phí vận chuyển</span>
                                <span class="summary-value"><?php echo number_format($shipping_fee, 0, ',', '.'); ?>₫</span>
                    </div>
                            <?php if ($shipping_fee === 0): ?>
                            <div class="shipping-note">
                                <i class="fas fa-check-circle"></i>
                                Bạn được miễn phí vận chuyển
                        </div>
                            <?php endif; ?>
                            <div class="summary-row total-row">
                                <span class="summary-label">Tổng cộng</span>
                                <span class="summary-value"><?php echo number_format($final_total, 0, ',', '.'); ?>₫</span>
                        </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-lock"></i>
                            Đặt hàng
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function handleSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            
            // Disable nút đặt hàng
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Đặt hàng thành công!', 'success');
                    // Chuyển hướng ngay lập tức
                    window.location.href = '/shoppingcart/orders/success';
                    return false;
                } else {
                    showToast(result.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-lock"></i> Đặt hàng';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Đặt hàng';
            }
            
            return false;
        }

        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Validate phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            const phone = e.target.value.replace(/\D/g, '');
            e.target.value = phone;
        });

        // Format currency
        function formatCurrency(number) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(number);
        }
    </script>
</body>
</html> 