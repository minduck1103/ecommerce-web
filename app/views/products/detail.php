<?php $this->partial('header'); ?>

<div class="container product-detail-container">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-images">
                <div class="main-image">
                    <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         id="mainImage">
                </div>
                <div class="thumbnail-images">
                    <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                         alt="Thumbnail 1" 
                         class="thumbnail active"
                         onclick="changeMainImage(this)">
                    <!-- Add more thumbnails here when you have multiple product images -->
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/shoppingcart">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="/shoppingcart/products">Sản phẩm</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
                    </ol>
                </nav>

                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-meta">
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span class="rating-count">(5 đánh giá)</span>
                    </div>
                    <div class="product-sku">
                        SKU: <?= htmlspecialchars($product['id']) ?>
                    </div>
                </div>

                <div class="product-price">
                    <?= number_format($product['price'], 0, ',', '.') ?>₫
                </div>

                <div class="product-description">
                    <?= htmlspecialchars($product['description']) ?>
                </div>

                <div class="product-variants">
                    <div class="size-selector">
                        <h3>Kích thước</h3>
                        <div class="size-options">
                            <button class="size-btn" data-size="S">S</button>
                            <button class="size-btn" data-size="M">M</button>
                            <button class="size-btn" data-size="L">L</button>
                            <button class="size-btn" data-size="XL">XL</button>
                        </div>
                    </div>

                    <div class="quantity-selector">
                        <h3>Số lượng</h3>
                        <div class="quantity-input">
                            <button class="quantity-btn minus" onclick="updateQuantity(-1)">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= $product['quantity'] ?>">
                            <button class="quantity-btn plus" onclick="updateQuantity(1)">+</button>
                        </div>
                    </div>
                </div>

                <div class="product-actions">
                    <button class="btn-add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">
                        <i class="fas fa-shopping-cart"></i>
                        Thêm vào giỏ hàng
                    </button>
                    <button class="btn-add-to-wishlist" onclick="addToWishlist(<?= $product['id'] ?>)">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <div class="product-additional-info">
                    <div class="info-item">
                        <i class="fas fa-truck"></i>
                        <span>Miễn phí vận chuyển cho đơn hàng từ 500.000₫</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-undo"></i>
                        <span>Đổi trả miễn phí trong vòng 30 ngày</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Bảo hành chính hãng 12 tháng</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="product-tabs">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description">Mô tả</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="specifications-tab" data-bs-toggle="tab" href="#specifications">Thông số</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#reviews">Đánh giá</a>
            </li>
        </ul>
        <div class="tab-content" id="productTabsContent">
            <div class="tab-pane fade show active" id="description">
                <div class="product-description-content">
                    <?= htmlspecialchars($product['description']) ?>
                </div>
            </div>
            <div class="tab-pane fade" id="specifications">
                <div class="product-specifications">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Thương hiệu</th>
                                <td>Brand Name</td>
                            </tr>
                            <tr>
                                <th>Xuất xứ</th>
                                <td>Việt Nam</td>
                            </tr>
                            <tr>
                                <th>Chất liệu</th>
                                <td>Cotton</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="reviews">
                <div class="product-reviews">
                    <!-- Add review content here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-detail-container {
    padding: 3rem 0;
}

/* Product Images */
.product-images {
    margin-bottom: 2rem;
}

.main-image {
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.thumbnail-images {
    display: flex;
    gap: 1rem;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    opacity: 0.6;
    transition: all 0.3s ease;
}

.thumbnail.active,
.thumbnail:hover {
    opacity: 1;
    border-color: #000;
}

/* Product Info */
.product-title {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.product-rating {
    color: #ffd700;
}

.rating-count {
    color: #666;
    margin-left: 0.5rem;
}

.product-price {
    font-size: 2rem;
    font-weight: 600;
    margin: 1.5rem 0;
}

.product-description {
    color: #666;
    margin-bottom: 2rem;
}

/* Variants */
.product-variants {
    margin-bottom: 2rem;
}

.product-variants h3 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.size-options {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.size-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #dee2e6;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.size-btn:hover,
.size-btn.active {
    background: #000;
    color: white;
    border-color: #000;
}

.quantity-input {
    display: flex;
    align-items: center;
    width: fit-content;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: none;
    cursor: pointer;
}

.quantity-input input {
    width: 60px;
    height: 40px;
    border: none;
    text-align: center;
    -moz-appearance: textfield;
}

.quantity-input input::-webkit-outer-spin-button,
.quantity-input input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Product Actions */
.product-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn-add-to-cart {
    flex: 1;
    padding: 1rem;
    background: #000;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add-to-cart:hover {
    background: #333;
}

.btn-add-to-wishlist {
    width: 50px;
    height: 50px;
    border: 1px solid #dee2e6;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add-to-wishlist:hover {
    background: #f8f9fa;
}

/* Additional Info */
.product-additional-info {
    border-top: 1px solid #dee2e6;
    padding-top: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    color: #666;
}

/* Product Tabs */
.product-tabs {
    margin-top: 4rem;
}

.nav-tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 2rem;
}

.nav-tabs .nav-link {
    color: #666;
    border: none;
    padding: 1rem 2rem;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #000;
    border-bottom: 2px solid #000;
}

.tab-content {
    padding: 2rem 0;
}

/* Responsive */
@media (max-width: 991px) {
    .product-images {
        margin-bottom: 2rem;
    }
}
</style>

<script>
function updateQuantity(change) {
    const input = document.getElementById('quantity');
    const newValue = parseInt(input.value) + change;
    if (newValue >= 1 && newValue <= <?= $product['quantity'] ?>) {
        input.value = newValue;
    }
}

function changeMainImage(thumbnail) {
    document.getElementById('mainImage').src = thumbnail.src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Size selection
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('add-to-cart-form');
    const quantityInput = document.getElementById('quantity');
    const sizeButtons = document.querySelectorAll('.size-btn');
    
    sizeButtons.forEach(button => {
        button.addEventListener('click', function() {
            sizeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('selected_size').value = this.dataset.size;
        });
    });

    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedSize = document.getElementById('selected_size').value;
        if (!selectedSize) {
            showNotification('Vui lòng chọn kích thước', 'error');
            return;
        }

        const formData = new FormData(this);
        
        fetch('/shoppingcart/cart/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Sản phẩm đã được thêm vào giỏ hàng', 'success');
                triggerCartUpdate(data.cartCount);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
        });
    });

    // Quantity controls
    document.querySelector('.quantity-down')?.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    document.querySelector('.quantity-up')?.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        const maxQuantity = parseInt(quantityInput.getAttribute('max'));
        if (currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
        } else {
            showNotification('Đã đạt số lượng tối đa có sẵn', 'error');
        }
    });

    quantityInput?.addEventListener('change', function() {
        const value = parseInt(this.value);
        const max = parseInt(this.getAttribute('max'));
        if (value < 1) {
            this.value = 1;
        } else if (value > max) {
            this.value = max;
            showNotification('Đã đạt số lượng tối đa có sẵn', 'error');
        }
    });
});

function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}
</script>

<?php $this->partial('footer'); ?> 