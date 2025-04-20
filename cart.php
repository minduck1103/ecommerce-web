<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get cart items
$cart_items = getCartItems($conn);
$cart_total = getCartTotal($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-dropdown {
            position: relative;
            display: inline-block;
        }
        .category-menu {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        .category-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .category-menu a:hover {
            background-color: #f1f1f1;
        }
        .category-dropdown:hover .category-menu {
            display: block;
        }
        .cart-item {
            transition: background-color 0.3s;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        .quantity-input {
            width: 70px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div class="category-dropdown">
                <a href="#" class="text-white me-3">
                    <i class="fas fa-bars fa-lg"></i>
                </a>
                <div class="category-menu">
                    <a href="products.php">Tất cả sản phẩm</a>
                    <?php
                    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
                    while ($cat = $stmt->fetch()) {
                        echo '<a href="products.php?category=' . htmlspecialchars($cat['slug']) . '">' . htmlspecialchars($cat['name']) . '</a>';
                    }
                    ?>
                </div>
            </div>
            <a class="navbar-brand mx-auto" href="index.php">ShopCart</a>
            <div class="d-flex align-items-center">
                <div class="search-container me-3" style="display: none;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="headerSearchInput" placeholder="Tìm kiếm sản phẩm...">
                        <button class="btn btn-outline-light" type="button" id="headerSearchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <a href="#" class="text-white me-3" id="searchIcon">
                    <i class="fas fa-search fa-lg"></i>
                </a>
                <a href="signin.php" class="text-white me-3">
                    <i class="fas fa-user fa-lg"></i>
                </a>
                <a href="cart.php" class="text-white">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="badge bg-danger rounded-pill ms-1"><?php echo count($_SESSION['cart']); ?></span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4">Giỏ hàng của bạn</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items -->
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Giỏ hàng trống</h5>
                            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                            <a href="products.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                        </div>
                        <?php else: ?>
                            <?php foreach ($cart_items as $item): ?>
                            <div class="row mb-3 cart-item p-3 border-bottom" data-product-id="<?php echo $item['id']; ?>">
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <p class="text-danger fw-bold"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group quantity-input">
                                        <button class="btn btn-outline-secondary btn-sm update-quantity" data-change="-1">-</button>
                                        <input type="text" class="form-control text-center quantity" value="<?php echo $item['quantity']; ?>" readonly>
                                        <button class="btn btn-outline-secondary btn-sm update-quantity" data-change="1">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-outline-danger btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="d-flex justify-content-between">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                    </a>
                    <?php if (!empty($cart_items)): ?>
                    <button class="btn btn-outline-danger" id="clearCart">
                        <i class="fas fa-trash me-2"></i>Xóa giỏ hàng
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tổng đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính</span>
                            <span><?php echo number_format($cart_total, 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển</span>
                            <span>30.000đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Giảm giá</span>
                            <span class="text-danger">-0đ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="fw-bold text-danger"><?php echo number_format($cart_total + 30000, 0, ',', '.'); ?>đ</span>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div class="mb-3">
                            <label for="coupon" class="form-label">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon" placeholder="Nhập mã">
                                <button class="btn btn-outline-primary" type="button">Áp dụng</button>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary w-100">Thanh toán</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle search input
        const searchIcon = document.getElementById('searchIcon');
        const searchContainer = document.querySelector('.search-container');
        
        searchIcon.addEventListener('click', function(e) {
            e.preventDefault();
            searchContainer.style.display = 'block';
            searchIcon.style.display = 'none';
            document.getElementById('headerSearchInput').focus();
        });

        // Handle search
        document.getElementById('headerSearchButton').addEventListener('click', function() {
            const searchInput = document.getElementById('headerSearchInput').value;
            if (searchInput) {
                window.location.href = `products.php?search=${encodeURIComponent(searchInput)}`;
            }
        });

        document.getElementById('headerSearchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchInput = this.value;
                if (searchInput) {
                    window.location.href = `products.php?search=${encodeURIComponent(searchInput)}`;
                }
            }
        });

        // Handle quantity updates
        document.querySelectorAll('.update-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const productId = cartItem.dataset.productId;
                const quantityInput = cartItem.querySelector('.quantity');
                const currentQuantity = parseInt(quantityInput.value);
                const change = parseInt(this.dataset.change);
                const newQuantity = currentQuantity + change;
                
                if (newQuantity > 0) {
                    fetch('ajax/update-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=${newQuantity}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            quantityInput.value = newQuantity;
                            // Update cart count in header
                            document.querySelector('.badge').textContent = data.cartCount;
                            // Update totals
                            updateTotals(data);
                        } else {
                            alert(data.message || 'Có lỗi xảy ra');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra');
                    });
                }
            });
        });

        // Handle remove item
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const productId = cartItem.dataset.productId;
                
                fetch('ajax/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=0`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItem.remove();
                        // Update cart count in header
                        document.querySelector('.badge').textContent = data.cartCount;
                        // Update totals
                        updateTotals(data);
                        // If cart is empty, reload page to show empty cart message
                        if (data.cartCount === 0) {
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            });
        });

        // Handle clear cart
        document.getElementById('clearCart')?.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm trong giỏ hàng?')) {
                fetch('ajax/clear-cart.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            }
        });

        // Update totals
        function updateTotals(data) {
            // Update subtotal
            document.querySelector('.card-body .d-flex:first-child span:last-child').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.subtotal) + 'đ';
            
            // Update total
            document.querySelector('.card-body .d-flex:last-child span:last-child').textContent = 
                new Intl.NumberFormat('vi-VN').format(data.subtotal + 30000) + 'đ';
        }
    </script>
</body>
</html> 