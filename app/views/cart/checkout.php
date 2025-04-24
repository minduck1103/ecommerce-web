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
            color: #666;
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

        .shipping-method,
        .payment-method {
            padding: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .shipping-method:hover,
        .payment-method:hover {
            border-color: #666;
            background-color: #f8f9fa;
        }

        .shipping-method.selected,
        .payment-method.selected {
            border-color: #666;
            background-color: #f8f9fa;
        }

        .method-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .method-icon i {
            font-size: 1.5rem;
            color: #666;
        }

        .method-icon img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .method-details {
            flex: 1;
        }

        .method-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .method-description {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .method-price {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
            text-align: right;
            white-space: nowrap;
        }

        .delivery-time {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }

        input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 0.5rem;
        }

        .payment-icons {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .payment-icons i {
            font-size: 2rem;
            color: #666;
        }

        .payment-icon {
            height: 24px;
            width: auto;
        }

        /* Style cho modal thành công */
        #orderSuccessModal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
        }

        #orderSuccessModal .success-icon {
            font-size: 4rem;
            color: var(--success-color);
            animation: scaleIn 0.5s ease;
        }

        #orderSuccessModal .modal-title {
            color: var(--text-color);
            font-weight: 700;
            font-size: 1.5rem;
        }

        #orderSuccessModal .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #orderSuccessModal .btn-outline-secondary {
            border: 2px solid var(--border-color);
            color: var(--text-color);
        }

        #orderSuccessModal .btn-outline-secondary:hover {
            background-color: var(--border-color);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        #orderSuccessModal .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), rgb(169, 177, 183));
            border: none;
        }

        #orderSuccessModal .btn-primary:hover {
            background: linear-gradient(135deg, rgb(83, 158, 163), var(--primary-color));
            transform: translateY(-2px);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="toast-container"></div>

    <!-- Modal thông báo đặt hàng thành công -->
    <div class="modal fade" id="orderSuccessModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="modal-title mb-3">Đặt hàng thành công!</h3>
                    <p class="text-muted mb-4">Cảm ơn bạn đã mua sắm tại Fashion Shop</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="window.location.href='/shoppingcart/products'">
                            <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                        </button>
                        <button type="button" class="btn btn-primary px-4" onclick="window.location.href='/shoppingcart/account/orders'">
                            <i class="fas fa-box me-2"></i>Xem đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <form id="checkoutForm" action="/shoppingcart/api/orders/create.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $final_total; ?>">
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
                            <div class="shipping-options">
                                <div class="shipping-method selected" onclick="selectShipping(this, 'standard')">
                                    <input type="radio" name="shipping_method" value="standard" checked>
                                    <div class="method-icon">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Giao hàng tiêu chuẩn</div>
                                        <p class="method-description">Giao hàng trong 3-5 ngày</p>
                                        <div class="delivery-time">Nhận hàng dự kiến: 23-25/03/2024</div>
                                    </div>
                                    <div class="method-price">30.000₫</div>
                                </div>

                                <div class="shipping-method" onclick="selectShipping(this, 'express')">
                                    <input type="radio" name="shipping_method" value="express">
                                    <div class="method-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Giao hàng nhanh</div>
                                        <p class="method-description">Giao hàng trong 1-2 ngày</p>
                                        <div class="delivery-time">Nhận hàng dự kiến: 21-22/03/2024</div>
                                    </div>
                                    <div class="method-price">45.000₫</div>
                                </div>

                                <div class="shipping-method" onclick="selectShipping(this, 'same_day')">
                                    <input type="radio" name="shipping_method" value="same_day">
                                    <div class="method-icon">
                                        <i class="fas fa-motorcycle"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Giao hàng trong ngày</div>
                                        <p class="method-description">Nhận hàng trong 2-6 giờ</p>
                                        <div class="delivery-time">Nhận hàng dự kiến: Hôm nay</div>
                                    </div>
                                    <div class="method-price">70.000₫</div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-section">
                            <div class="section-title">
                                <i class="fas fa-credit-card"></i>
                                Phương thức thanh toán
                            </div>
                            <div class="payment-options">
                                <div class="payment-method selected" onclick="selectPayment(this, 'cod')">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div class="method-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Thanh toán khi nhận hàng (COD)</div>
                                        <p class="method-description">Thanh toán bằng tiền mặt khi nhận hàng</p>
                                    </div>
                                </div>

                                <div class="payment-method" onclick="selectPayment(this, 'bank_transfer')">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <div class="method-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Chuyển khoản ngân hàng</div>
                                        <p class="method-description">Chuyển khoản qua tài khoản ngân hàng</p>
                                        <div class="payment-icons">
                                            <i class="fas fa-credit-card"></i>
                                            <i class="fab fa-cc-visa"></i>
                                            <i class="fab fa-cc-mastercard"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-method" onclick="selectPayment(this, 'e_wallet')">
                                    <input type="radio" name="payment_method" value="e_wallet">
                                    <div class="method-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Ví điện tử</div>
                                        <p class="method-description">Thanh toán qua ví điện tử</p>
                                        <div class="payment-icons">
                                            <i class="fas fa-qrcode" title="Momo"></i>
                                            <i class="fas fa-wallet" title="ZaloPay"></i>
                                            <i class="fas fa-money-check" title="VNPay"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-method" onclick="selectPayment(this, 'installment')">
                                    <input type="radio" name="payment_method" value="installment">
                                    <div class="method-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="method-details">
                                        <div class="method-title">Trả góp</div>
                                        <p class="method-description">Trả góp qua thẻ tín dụng hoặc công ty tài chính</p>
                                        <div class="payment-icons">
                                            <i class="fab fa-cc-visa"></i>
                                            <i class="fab fa-cc-mastercard"></i>
                                            <i class="fab fa-cc-jcb"></i>
                                        </div>
                                    </div>
                                </div>
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

                        <button type="button" class="btn btn-primary" id="submitBtn">
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
        const form = document.getElementById('checkoutForm');
        const submitBtn = document.getElementById('submitBtn');

        // Xử lý sự kiện click nút đặt hàng
        submitBtn.addEventListener('click', async function(e) {
            e.preventDefault(); // Ngăn form submit mặc định
            
            // Validate form trước khi submit
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            try {
                // Disable nút để tránh double-click
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                // Lấy giá trị shipping fee từ phương thức vận chuyển đã chọn
                const selectedShippingMethod = document.querySelector('input[name="shipping_method"]:checked').value;
                const shippingFee = getShippingFee(selectedShippingMethod);

                // Lấy tổng tiền từ summary
                const totalAmount = parseInt(document.querySelector('.total-row .summary-value').textContent.replace(/[^\d]/g, ''));

                const data = {
                    fullname: form.querySelector('#fullname').value,
                    phone: form.querySelector('#phone').value,
                    email: form.querySelector('#email').value,
                    address: form.querySelector('#address').value,
                    shipping_method: selectedShippingMethod,
                    payment_method: document.querySelector('input[name="payment_method"]:checked').value,
                    shipping_fee: shippingFee,
                    total_amount: totalAmount
                };

                const response = await fetch('/shoppingcart/api/orders/create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Chuyển hướng đến trang thành công
                    window.location.href = '/shoppingcart/orders/success';
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
        });

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

        function getShippingFee(method) {
            switch(method) {
                case 'standard':
                    return 30000;
                case 'express':
                    return 45000;
                case 'same_day':
                    return 70000;
                default:
                    return 30000;
            }
        }

        function selectShipping(element, method) {
            document.querySelectorAll('.shipping-method').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
            
            // Cập nhật phí vận chuyển và tổng tiền
            const shippingFee = getShippingFee(method);
            const subtotal = parseInt(document.querySelector('.summary-row:first-child .summary-value').textContent.replace(/[^\d]/g, ''));
            const total = subtotal + shippingFee;
            
            // Cập nhật hiển thị
            document.querySelector('.summary-row:nth-child(2) .summary-value').textContent = 
                new Intl.NumberFormat('vi-VN').format(shippingFee) + '₫';
            document.querySelector('.total-row .summary-value').textContent = 
                new Intl.NumberFormat('vi-VN').format(total) + '₫';
        }

        function selectPayment(element, method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html> 