<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $cart_items = [];
    $total = 0;
    $total_items = 0;
    
    if (!empty($_SESSION['cart'])) {
        // Lấy tất cả ID sản phẩm trong giỏ hàng
        $product_ids = array_keys($_SESSION['cart']);
        
        if (!empty($product_ids)) {
            // Sử dụng Prepared Statement để lấy thông tin sản phẩm
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            $sql = "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Tạo mảng cart_items với thông tin đầy đủ
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
                    $total_items++;
                }
            }
        }
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error_message = "Đã có lỗi xảy ra khi tải giỏ hàng. Vui lòng thử lại sau.";
}

// Tính phí vận chuyển (có thể thay đổi logic theo yêu cầu)
$shipping_fee = $total >= 1000000 ? 0 : 30000;
$final_total = $total + $shipping_fee;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --border-color: #e0e0e0;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .cart-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .cart-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary-color);
        }

        .cart-empty {
            background: white;
            padding: 3rem;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-empty i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        .cart-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .cart-item:hover .item-image {
            transform: scale(1.05);
        }

        .item-details {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .item-info {
            flex-grow: 1;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
        }

        .item-name:hover {
            color: var(--secondary-color);
        }

        .item-price {
            font-size: 1.1rem;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .item-subtotal {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .quantity-controls {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .quantity-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: white;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: var(--background-color);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 50px;
            height: 36px;
            border: none;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            text-align: center;
            font-size: 1rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        .remove-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--secondary-color);
            background: white;
            color: var(--secondary-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remove-btn:hover {
            background: var(--secondary-color);
            color: white;
        }

        .cart-summary {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .summary-label {
            color: var(--text-color);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .shipping-row {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .free-shipping-msg {
            color: var(--success-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .summary-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
        }

        .summary-total .summary-label {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .summary-total .summary-value {
            font-size: 1.4rem;
            color: var(--secondary-color);
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            margin-top: 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .checkout-btn:hover {
            background: #34495e;
            transform: translateY(-2px);
        }

        .cart-empty {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-empty i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1.5rem;
        }

        .cart-empty h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .cart-empty p {
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .continue-shopping {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .continue-shopping:hover {
            background: #34495e;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .item-details {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-image {
                width: 100%;
                height: 200px;
            }

            .quantity-controls {
                margin: 1rem 0;
            }

            .cart-summary {
                margin-top: 2rem;
                position: static;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Giỏ hàng của bạn</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php elseif (empty($cart_items)): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
        <h3>Giỏ hàng trống</h3>
                <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="/shoppingcart/products" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i>
                    Tiếp tục mua sắm
                </a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-id="<?= $item['id'] ?>">
                            <div class="item-details">
                                <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="item-image"
                                     onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                <div class="item-info">
                                    <a href="/shoppingcart/products/detail/<?= $item['id'] ?>" class="item-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <div class="item-price">
                                        Đơn giá: <?= number_format($item['price'], 0, ',', '.') ?>₫
                                            </div>
                                    <div class="quantity-controls">
                                        <button type="button" 
                                                class="quantity-btn" 
                                                onclick="updateQuantity(<?= $item['id'] ?>, 'decrease')"
                                                <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="99"
                                               data-price="<?= $item['price'] ?>"
                                               onchange="handleQuantityChange(this, <?= $item['id'] ?>)">
                                        <button type="button" 
                                                class="quantity-btn" 
                                                onclick="updateQuantity(<?= $item['id'] ?>, 'increase')"
                                                <?= $item['quantity'] >= 99 ? 'disabled' : '' ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="item-subtotal">
                                        Tổng: <?= number_format($item['subtotal'], 0, ',', '.') ?>₫
                                    </div>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                    Xóa
                                </button>
                            </div>
                        </div>
                                <?php endforeach; ?>
                </div>
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h2 class="summary-title">Tổng giỏ hàng</h2>
                        <div class="summary-row">
                            <div class="summary-label">
                                <i class="fas fa-shopping-basket"></i>
                                Tạm tính
                            </div>
                            <div class="summary-value"><?= number_format($total, 0, ',', '.') ?>₫</div>
                        </div>
                        <div class="summary-row shipping-row">
                            <div class="summary-label">
                                <i class="fas fa-truck"></i>
                                Phí vận chuyển
                            </div>
                            <div class="summary-value">
                                <?= $shipping_fee > 0 ? number_format($shipping_fee, 0, ',', '.') . '₫' : 'Miễn phí' ?>
                            </div>
                            <?php if ($shipping_fee > 0): ?>
                                <div class="free-shipping-msg">
                                    <i class="fas fa-info-circle"></i>
                                    Mua thêm <?= number_format(1000000 - $total, 0, ',', '.') ?>₫ để được miễn phí vận chuyển
            </div>
                            <?php endif; ?>
        </div>
                        <div class="summary-row summary-total">
                            <div class="summary-label">
                                <i class="fas fa-receipt"></i>
                                Tổng cộng
                    </div>
                            <div class="summary-value"><?= number_format($final_total, 0, ',', '.') ?>₫</div>
                    </div>
                        <button class="checkout-btn" onclick="window.location.href='/shoppingcart/cart/checkout'">
                            Tiến hành thanh toán
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="toast-container"></div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const container = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                <span>${message}</span>
            `;
            container.appendChild(toast);

            // Thêm hiệu ứng fade in
            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => container.removeChild(toast), 300);
            }, 3000);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount).replace('₫', '') + '₫';
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function updateItemSubtotal(productId) {
            const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (!item) return;

            const input = item.querySelector('.quantity-input');
            const price = parseFloat(input.dataset.price);
            const quantity = parseInt(input.value);
            const subtotal = price * quantity;
            
            const subtotalElement = item.querySelector('.item-subtotal');
            if (subtotalElement) {
                subtotalElement.textContent = `Tổng: ${formatCurrency(subtotal)}`;
                // Thêm hiệu ứng highlight khi giá thay đổi
                subtotalElement.classList.add('price-updated');
                setTimeout(() => subtotalElement.classList.remove('price-updated'), 500);
            }
        }

        function updateCartSummary() {
            let total = 0;
            let itemCount = 0;

            document.querySelectorAll('.cart-item').forEach(item => {
                const input = item.querySelector('.quantity-input');
                const price = parseFloat(input.dataset.price);
                const quantity = parseInt(input.value);
                total += price * quantity;
                itemCount += quantity;
            });

            const shippingFee = total >= 1000000 ? 0 : 30000;
            const finalTotal = total + shippingFee;

            // Cập nhật tạm tính
            const subtotalElement = document.querySelector('.summary-row:first-child .summary-value');
            if (subtotalElement) {
                subtotalElement.textContent = formatCurrency(total);
                subtotalElement.classList.add('price-updated');
                setTimeout(() => subtotalElement.classList.remove('price-updated'), 500);
            }

            // Cập nhật phí vận chuyển
            const shippingElement = document.querySelector('.shipping-row .summary-value');
            if (shippingElement) {
                shippingElement.textContent = shippingFee > 0 ? formatCurrency(shippingFee) : 'Miễn phí';
            }

            // Cập nhật thông báo miễn phí vận chuyển
            const freeShippingMsg = document.querySelector('.free-shipping-msg');
            if (freeShippingMsg) {
                if (total < 1000000) {
                    const remaining = 1000000 - total;
                    freeShippingMsg.innerHTML = `
                        <i class="fas fa-info-circle"></i>
                        Mua thêm ${formatCurrency(remaining)} để được miễn phí vận chuyển
                    `;
                    freeShippingMsg.style.display = 'block';
                } else {
                    freeShippingMsg.style.display = 'none';
                }
            }

            // Cập nhật tổng cộng
            const totalElement = document.querySelector('.summary-total .summary-value');
            if (totalElement) {
                totalElement.textContent = formatCurrency(finalTotal);
                totalElement.classList.add('price-updated');
                setTimeout(() => totalElement.classList.remove('price-updated'), 500);
            }

            // Cập nhật số lượng trong icon giỏ hàng
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = itemCount;
            }
        }

        function updateQuantity(productId, action) {
            const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (!item) return;

            const input = item.querySelector('.quantity-input');
            let quantity = parseInt(input.value);
            const oldQuantity = quantity;

            if (action === 'increase' && quantity < 99) {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            } else {
                return; // Không cần cập nhật nếu không thay đổi
            }

            handleQuantityChange(input, productId, quantity, oldQuantity);
        }

        const debouncedUpdateServer = debounce((productId, quantity, oldQuantity) => {
            fetch('/shoppingcart/app/api/cart/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Đã cập nhật số lượng');
                    updateCartCount();
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                    restoreQuantity(productId, oldQuantity);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi cập nhật số lượng', 'error');
                restoreQuantity(productId, oldQuantity);
            });
        }, 500);

        function restoreQuantity(productId, oldQuantity) {
            const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (!item) return;

            const input = item.querySelector('.quantity-input');
            input.value = oldQuantity;
            updateItemSubtotal(productId);
            updateCartSummary();
            updateQuantityButtons(item, oldQuantity);
        }

        function updateQuantityButtons(item, quantity) {
            const decreaseBtn = item.querySelector('.quantity-btn:first-child');
            const increaseBtn = item.querySelector('.quantity-btn:last-child');
            
            if (decreaseBtn) decreaseBtn.disabled = quantity <= 1;
            if (increaseBtn) increaseBtn.disabled = quantity >= 99;
        }

        function handleQuantityChange(input, productId, newQuantity, oldQuantity) {
            if (!input || isNaN(newQuantity)) return;

            const quantity = Math.min(Math.max(parseInt(newQuantity) || 1, 1), 99);
            const item = input.closest('.cart-item');
            
            if (!item) return;

            // Cập nhật giao diện
            input.value = quantity;
            updateQuantityButtons(item, quantity);
            updateItemSubtotal(productId);
            updateCartSummary();

            // Gửi cập nhật lên server
            debouncedUpdateServer(productId, quantity, oldQuantity || quantity);
        }

        function removeItem(productId) {
            const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (!item) return;

            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                return;
            }

            item.classList.add('removing');
            const removeBtn = item.querySelector('.remove-btn');
            if (removeBtn) {
                removeBtn.disabled = true;
                removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
            }

            fetch('/shoppingcart/app/api/cart/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        item.remove();
                        updateCartSummary();
                        updateCartCount();
                        
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload();
                        }
                        
                        showToast('Đã xóa sản phẩm khỏi giỏ hàng');
                    }, 300);
                } else {
                    item.classList.remove('removing');
                    if (removeBtn) {
                        removeBtn.disabled = false;
                        removeBtn.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                    }
                    showToast(data.message || 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                item.classList.remove('removing');
                if (removeBtn) {
                    removeBtn.disabled = false;
                    removeBtn.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                }
                showToast('Có lỗi xảy ra khi xóa sản phẩm', 'error');
            });
        }

        function updateCartCount() {
            fetch('/shoppingcart/app/api/cart/count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.count;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }

        // Thêm style cho hiệu ứng
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }

            .toast {
                background: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 8px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            }

            .toast.show {
                opacity: 1;
                transform: translateX(0);
            }

            .toast.success {
                border-left: 4px solid var(--success-color);
            }

            .toast.error {
                border-left: 4px solid var(--secondary-color);
            }

            .toast i {
                font-size: 1.2rem;
            }

            .toast.success i {
                color: var(--success-color);
            }

            .toast.error i {
                color: var(--secondary-color);
            }

            .price-updated {
                animation: highlight 0.5s ease;
            }

            @keyframes highlight {
                0% { background-color: rgba(46, 204, 113, 0.2); }
                100% { background-color: transparent; }
            }

            .cart-item.removing {
                opacity: 0.7;
                pointer-events: none;
            }

            .cart-item {
                transition: all 0.3s ease;
            }

            .quantity-controls {
                position: relative;
            }

            .quantity-btn:active {
                transform: scale(0.95);
            }

            .quantity-input:focus {
                outline: 2px solid var(--primary-color);
                outline-offset: -1px;
            }
        `;
        document.head.appendChild(style);

        // Khởi tạo khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            updateCartSummary();
            updateCartCount();
        });
</script> 
</body>
</html> 