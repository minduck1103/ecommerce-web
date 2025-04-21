<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="/shoppingcart/public/css/style.css">
</head>
<body>
    <?php require_once APP_ROOT . '/views/partials/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <!-- Thông tin thanh toán -->
            <div class="col-lg-8">
                <div class="checkout-form">
                    <h2 class="mb-4">Thông tin thanh toán</h2>
                    <form id="checkoutForm" method="POST" action="/shoppingcart/orders/create">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Họ</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Tên</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>

                        <div class="mb-4">
                            <h4>Phương thức thanh toán</h4>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">
                                    Thanh toán khi nhận hàng (COD)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="banking" value="banking">
                                <label class="form-check-label" for="banking">
                                    Chuyển khoản ngân hàng
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="momo" value="momo">
                                <label class="form-check-label" for="momo">
                                    Ví MoMo
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Đặt hàng</button>
                    </form>
                </div>
            </div>

            <!-- Tổng quan đơn hàng -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h3 class="mb-4">Tổng quan đơn hàng</h3>
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($item['product']['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['product']['name']) ?>">
                            </div>
                            <div class="item-details">
                                <h5><?= htmlspecialchars($item['product']['name']) ?></h5>
                                <p>Số lượng: <?= $item['quantity'] ?></p>
                                <p class="item-price"><?= number_format($item['subtotal'], 0, ',', '.') ?>₫</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính</span>
                            <span><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển</span>
                            <span><?= number_format($shipping, 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="d-flex justify-content-between total">
                            <strong>Tổng cộng</strong>
                            <strong><?= number_format($total, 0, ',', '.') ?>₫</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_ROOT . '/views/partials/footer.php'; ?>

    <style>
    .checkout-form {
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .order-summary {
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 2rem;
    }

    .cart-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eee;
    }

    .item-image {
        width: 80px;
        height: 80px;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }

    .item-details {
        flex: 1;
    }

    .item-details h5 {
        margin: 0 0 0.5rem;
        font-size: 1rem;
    }

    .item-details p {
        margin: 0;
        color: #666;
    }

    .item-price {
        color: #4CAF50 !important;
        font-weight: 600;
    }

    .order-totals {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 2px solid #eee;
    }

    .total {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
        font-size: 1.2rem;
    }

    .form-control:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }

    .btn-primary {
        background-color: #4CAF50;
        border-color: #4CAF50;
    }

    .btn-primary:hover {
        background-color: #45a049;
        border-color: #45a049;
    }
    </style>

    <script>
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        fetch('/shoppingcart/orders/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Đặt hàng thành công!', 'success');
                setTimeout(() => {
                    window.location.href = '/shoppingcart/orders/success/' + data.orderId;
                }, 1500);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi đặt hàng', 'error');
        });
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.textContent = message;
        
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: '9999',
            padding: '15px 25px',
            borderRadius: '4px',
            animation: 'slideIn 0.3s ease-out'
        });
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    </script>
</body>
</html> 