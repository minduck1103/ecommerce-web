<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Thêm header để kiểm soát cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Debug mode - Có thể bật/tắt bằng query parameter ?debug=1
$debug_mode = isset($_GET['debug']) && $_GET['debug'] == '1';

// Debug function
function debug_print($data, $title = '') {
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
        echo '<div class="debug-section">';
        if ($title) {
            echo "<h3>$title</h3>";
        }
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        echo '</div>';
    }
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/shoppingcart/cart';
    ?>
    <script>
        alert('Vui lòng đăng nhập để xem giỏ hàng');
        window.location.href = '/shoppingcart/auth/login';
    </script>
    <?php
    exit;
}

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
                $quantity = (int)$_SESSION['cart'][$product['id']];
                $price = (float)$product['price'];
                $cart_items[] = array_merge($product, ['quantity' => $quantity]);
                $total += $price * $quantity;
                $total_items += $quantity;
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Có lỗi xảy ra: " . $e->getMessage();
}

// Tính phí vận chuyển (có thể thay đổi logic theo yêu cầu)
$shipping_fee = $total >= 1000000 ? 0 : 30000;
$final_total = $total + $shipping_fee;

// Include header
require_once __DIR__ . '/../partials/header.php';

?>

<div class="container my-5">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php elseif (empty($cart_items)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Giỏ hàng trống</h2>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng.<br>Hãy khám phá những sản phẩm tuyệt vời của chúng tôi!</p>
            <a href="/shoppingcart/products" class="continue-shopping">
                <i class="fas fa-arrow-left"></i>
                Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['id'] ?>">
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
                                            class="quantity-btn decrease" 
                                            data-action="decrease">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="quantity-input" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="99"
                                           data-price="<?= $item['price'] ?>"
                                           data-product-id="<?= $item['id'] ?>">
                                    <button type="button" 
                                            class="quantity-btn increase"
                                            data-action="increase">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="item-subtotal" data-price="<?= $item['price'] * $item['quantity'] ?>">
                                    Tổng: <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫
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
                    <h2 class="summary-title">Tổng đơn hàng</h2>
                    <div class="summary-row">
                        <div class="summary-label">
                            <i class="fas fa-shopping-basket"></i>
                            Tạm tính
                        </div>
                        <div class="summary-value"><?= number_format($total, 0, ',', '.') ?>₫</div>
                    </div>
                    <div class="shipping-row">
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
                    <div class="summary-total">
                        <div class="summary-label">
                            <i class="fas fa-receipt"></i>
                            Tổng thanh toán
                        </div>
                        <div class="summary-value"><?= number_format($final_total, 0, ',', '.') ?>₫</div>
                    </div>
                    <a href="/shoppingcart/cart/checkout" class="checkout-btn">
                        Thanh toán ngay
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="toast-message"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Confirm Dialog Container -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Xác nhận
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

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
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .empty-cart {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 2rem auto;
    }

    .empty-cart-icon {
        width: 150px;
        height: 150px;
        background: var(--background-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        position: relative;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .empty-cart-icon i {
        font-size: 4rem;
        color: var(--primary-color);
        opacity: 0.7;
    }

    .empty-cart h2 {
        color: var(--primary-color);
        font-size: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .empty-cart p {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .continue-shopping {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-color);
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .continue-shopping:hover {
        background: #34495e;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        color: white;
    }

    .continue-shopping i {
        font-size: 1.2rem;
    }

    .suggested-products {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .suggested-products h3 {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 0 1rem;
    }

    @media (max-width: 768px) {
        .empty-cart {
            margin: 1rem;
            padding: 2rem 1rem;
        }

        .empty-cart-icon {
            width: 120px;
            height: 120px;
        }

        .empty-cart-icon i {
            font-size: 3rem;
        }

        .empty-cart h2 {
            font-size: 1.5rem;
        }

        .empty-cart p {
            font-size: 1rem;
        }

        .continue-shopping {
            width: 100%;
            justify-content: center;
        }
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
        background: #ffffff;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.06);
        position: sticky;
        top: 2rem;
        border: 1px solid #f0f0f0;
    }

    .summary-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
        letter-spacing: 0.5px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.75rem 0;
    }

    .summary-label {
        color: #666;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
    }

    .summary-label i {
        color: #888;
        font-size: 1rem;
    }

    .summary-value {
        font-weight: 600;
        color: #333;
        font-size: 1rem;
    }

    .shipping-row {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 12px;
        margin: 1.25rem 0;
    }

    .shipping-row .summary-label i {
        color: #666;
    }

    .free-shipping-msg {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #eee;
        color: #666;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .free-shipping-msg i {
        color: #888;
    }

    .summary-total {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid #eee;
    }

    .summary-total .summary-label {
        color: #333;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .summary-total .summary-value {
        color: #111;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .checkout-btn {
        display: block;
        width: 100%;
        padding: 1rem 1.5rem;
        margin-top: 1.5rem;
        background: #333;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        text-align: center;
        letter-spacing: 0.5px;
    }

    .checkout-btn:hover {
        background: #222;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .checkout-btn i {
        margin-left: 0.5rem;
        font-size: 0.9rem;
        transition: transform 0.2s ease;
    }

    .checkout-btn:hover i {
        transform: translateX(3px);
    }

    @media (max-width: 768px) {
        .cart-summary {
            margin-top: 2rem;
            position: static;
            padding: 1.5rem;
        }

        .summary-title {
            font-size: 1.2rem;
        }

        .summary-total .summary-value {
            font-size: 1.3rem;
        }
    }

    .toast {
        background: white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    #confirmToast {
        min-width: 300px;
    }
    
    #confirmToast .toast-header {
        border-bottom: none;
        padding: 0.75rem 1rem;
    }
    
    #confirmToast .toast-body {
        padding: 1rem;
    }
    
    .btn-sm {
        padding: 0.25rem 1rem;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        padding: 1rem 1.5rem;
    }

    .modal-body {
        font-size: 1.1rem;
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
    }

    .btn-secondary {
        background-color: #6c757d;
        border: none;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-danger {
        background-color: #dc3545;
        border: none;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .modal-backdrop.show {
        opacity: 0.5;
    }
</style>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Khởi tạo toast và modal
    const toast = new bootstrap.Toast(document.getElementById('toast'));
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    // Hàm hiển thị toast message
    function showToast(message, type = 'success') {
        const toastElement = document.getElementById('toast');
        const messageElement = document.getElementById('toast-message');
        
        messageElement.textContent = message;
        toastElement.classList.remove('bg-success', 'bg-danger');
        toastElement.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
        
        toast.show();
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount).replace('₫', '') + '₫';
    }

    function updateCartSummary() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(element => {
            const price = parseFloat(element.dataset.price);
            if (!isNaN(price)) {
                total += price;
            }
        });

        const shippingFee = total >= 1000000 ? 0 : 30000;
        const finalTotal = total + shippingFee;

        // Cập nhật tạm tính với animation
        const subtotalElement = document.querySelector('.summary-row:first-child .summary-value');
        if (subtotalElement) {
            subtotalElement.textContent = formatCurrency(total);
            subtotalElement.classList.add('price-update');
            setTimeout(() => subtotalElement.classList.remove('price-update'), 500);
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

        // Cập nhật tổng cộng với animation
        const totalElement = document.querySelector('.summary-total .summary-value');
        if (totalElement) {
            totalElement.textContent = formatCurrency(finalTotal);
            totalElement.classList.add('price-update');
            setTimeout(() => totalElement.classList.remove('price-update'), 500);
        }
    }

    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }

    // Biến lưu trữ thông tin sản phẩm đang được xóa
    let currentDeleteItem = null;

    // Xử lý xóa sản phẩm
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            currentDeleteItem = {
                element: cartItem,
                id: cartItem.dataset.productId
            };
            confirmModal.show();
        });
    });

    // Xử lý sự kiện khi người dùng xác nhận xóa
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!currentDeleteItem) return;

        const { element, id } = currentDeleteItem;
        
        fetch('/shoppingcart/app/api/cart/remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            confirmModal.hide();
            
            if (data.success) {
                element.remove();
                updateCartSummary();
                updateCartCount(data.cart_count);
                showToast('Đã xóa sản phẩm khỏi giỏ hàng');
                
                if (data.cart_count === 0) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                showToast(data.message || 'Có lỗi xảy ra khi xóa sản phẩm', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            confirmModal.hide();
            showToast('Có lỗi xảy ra khi xóa sản phẩm', 'danger');
        });
    });

    // Khởi tạo khi trang được tải
    document.addEventListener('DOMContentLoaded', function() {
        updateCartSummary();
    });

    function updateItemQuantity(productId, action) {
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        if (!input) return;

        const currentQty = parseInt(input.value);
        let newQty = currentQty;

        if (action === 'increase' && currentQty < 99) {
            newQty = currentQty + 1;
        } else if (action === 'decrease' && currentQty > 1) {
            newQty = currentQty - 1;
        } else {
            return;
        }

        updateQuantity(productId, newQty);
    }

    function updateQuantity(productId, quantity) {
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        if (!input) return;

        const item = input.closest('.cart-item');
        const price = parseFloat(input.dataset.price);
        const subtotalElement = item.querySelector('.item-subtotal');
        const decreaseBtn = item.querySelector('.quantity-btn.decrease');
        const increaseBtn = item.querySelector('.quantity-btn.increase');

        // Cập nhật UI trước
        input.value = quantity;
        const subtotal = price * quantity;
        subtotalElement.textContent = `Tổng: ${formatCurrency(subtotal)}`;
        subtotalElement.dataset.price = subtotal;

        // Cập nhật trạng thái nút
        decreaseBtn.disabled = quantity <= 1;
        increaseBtn.disabled = quantity >= 99;

        // Gửi request cập nhật lên server
        fetch('/shoppingcart/app/api/cart/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartSummary();
                showToast('Đã cập nhật số lượng');
            } else {
                // Khôi phục giá trị cũ nếu có lỗi
                input.value = input.defaultValue;
                showToast(data.message || 'Có lỗi xảy ra khi cập nhật số lượng', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Khôi phục giá trị cũ nếu có lỗi
            input.value = input.defaultValue;
            showToast('Có lỗi xảy ra khi cập nhật số lượng', 'danger');
        });
    }

    // Xử lý sự kiện cho nút tăng/giảm số lượng
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.closest('.cart-item').dataset.productId;
                const action = this.dataset.action;
                updateItemQuantity(productId, action);
            });
        });

        // Xử lý sự kiện khi người dùng nhập số lượng trực tiếp
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                let quantity = parseInt(this.value);

                // Kiểm tra giới hạn số lượng
                if (isNaN(quantity) || quantity < 1) quantity = 1;
                if (quantity > 99) quantity = 99;

                updateQuantity(productId, quantity);
            });
        });
    });
</script> 