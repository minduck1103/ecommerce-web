<div class="container py-5">
    <h1 class="mb-4">Giỏ hàng của bạn</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h3>Giỏ hàng trống</h3>
        <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
        <a href="/shoppingcart/products" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                <tr data-product-id="<?= $item['product']['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($item['product']['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                                 class="cart-product-image">
                                            <div class="ml-3">
                                                <h5 class="mb-0"><?= htmlspecialchars($item['product']['name']) ?></h5>
                                                <small class="text-muted"><?= htmlspecialchars($item['product']['category_name']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['product']['price'], 0, ',', '.') ?>₫</td>
                                    <td>
                                        <div class="quantity-control">
                                            <button class="btn btn-sm btn-outline-secondary quantity-btn" data-action="decrease">-</button>
                                            <input type="number" class="form-control quantity-input" value="<?= $item['quantity'] ?>" min="1">
                                            <button class="btn btn-sm btn-outline-secondary quantity-btn" data-action="increase">+</button>
                                        </div>
                                    </td>
                                    <td class="subtotal"><?= number_format($item['subtotal'], 0, ',', '.') ?>₫</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger remove-item">
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tổng giỏ hàng</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tạm tính:</span>
                        <span class="cart-total"><?= number_format($total, 0, ',', '.') ?>₫</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="cart-total"><?= number_format($total, 0, ',', '.') ?>₫</strong>
                    </div>
                    <a href="/shoppingcart/order/checkout" class="btn btn-primary btn-block">Tiến hành thanh toán</a>
                    <a href="/shoppingcart/products" class="btn btn-outline-secondary btn-block mt-2">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.cart-product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-input {
    width: 60px;
    text-align: center;
}

.quantity-btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .cart-product-image {
        width: 60px;
        height: 60px;
    }
    
    .quantity-control {
        flex-direction: column;
        gap: 0.25rem;
    }
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

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('.quantity-input');
            const action = this.dataset.action;
            let value = parseInt(input.value);
            
            if (action === 'increase') {
                value++;
            } else if (action === 'decrease' && value > 1) {
                value--;
            }
            
            input.value = value;
            updateCartItem(row.dataset.productId, value);
        });
    });
    
    // Update on input change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            let value = parseInt(this.value);
            
            if (value < 1) {
                value = 1;
                this.value = 1;
            }
            
            updateCartItem(row.dataset.productId, value);
        });
    });
    
    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                const row = this.closest('tr');
                const productId = row.dataset.productId;
                
                updateCartItem(productId, 0);
            }
        });
    });
});

function updateCartItem(productId, quantity) {
    fetch('/shoppingcart/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (quantity === 0) {
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                row.remove();
                showNotification('Sản phẩm đã được xóa khỏi giỏ hàng', 'success');
                
                if (document.querySelectorAll('tr[data-product-id]').length === 0) {
                    location.reload(); // Reload if cart is empty
                }
            } else {
                // Cập nhật tổng giá tiền của sản phẩm
                const subtotalElement = document.querySelector(`tr[data-product-id="${productId}"] .subtotal`);
                subtotalElement.textContent = formatPrice(data.subtotal);
                
                // Cập nhật tổng giỏ hàng
                document.querySelectorAll('.cart-total').forEach(element => {
                    element.textContent = formatPrice(data.total);
                });
                
                // Cập nhật số lượng trong header
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cart_count;
                }
                
                showNotification('Giỏ hàng đã được cập nhật', 'success');
            }
            
            // Cập nhật tổng số lượng và tổng tiền khi xóa sản phẩm
            if (data.cart_count === 0) {
                location.reload();
            }
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi cập nhật giỏ hàng', 'error');
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

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