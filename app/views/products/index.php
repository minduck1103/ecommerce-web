<!-- Banner Section -->
<section class="banner-section">
    <div class="banner-container">
        <img src="/shoppingcart/public/images/products-banner.jpg" alt="Products Banner" class="banner-image">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <h1 class="banner-title">Sản phẩm của chúng tôi</h1>
            <p class="banner-subtitle">Khám phá bộ sưu tập đa dạng và phong cách</p>
        </div>
    </div>
</section>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="filter-section">
                <h3>Bộ lọc sản phẩm</h3>
                
                <!-- Category Filter -->
                <div class="filter-group">
                    <h4>Danh mục</h4>
                    <div class="filter-options">
                        <?php foreach ($categories as $category): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   value="<?= htmlspecialchars($category['slug']) ?>" 
                                   id="category<?= htmlspecialchars($category['id']) ?>">
                            <label class="form-check-label" for="category<?= htmlspecialchars($category['id']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-group">
                    <h4>Khoảng giá</h4>
                    <div class="price-range">
                        <input type="number" class="form-control" placeholder="Từ" id="priceFrom">
                        <span>-</span>
                        <input type="number" class="form-control" placeholder="Đến" id="priceTo">
                    </div>
                    <button class="btn btn-primary mt-2" onclick="applyFilters()">Áp dụng</button>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="products-header">
                <h2>Tất cả sản phẩm</h2>
                <div class="sort-options">
                    <select class="form-select" id="sortSelect" onchange="applyFilters()">
                        <option value="newest">Mới nhất</option>
                        <option value="price-asc">Giá tăng dần</option>
                        <option value="price-desc">Giá giảm dần</option>
                    </select>
                </div>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                            <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <div class="product-actions">
                            <button class="action-btn" onclick="addToCart(<?= $product['id'] ?>)">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <button class="action-btn" onclick="addToWishlist(<?= $product['id'] ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>
                        <div class="product-category">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </div>
                        <div class="product-price">
                            <?= number_format($product['price'], 0, ',', '.') ?>₫
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Banner Section */
.banner-section {
    margin-top: -80px;
    height: 60vh;
    position: relative;
    margin-bottom: 3rem;
}

.banner-container {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.banner-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    z-index: 2;
}

.banner-title {
    font-size: 3rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.banner-subtitle {
    font-size: 1.2rem;
    margin-bottom: 2rem;
}

/* Existing styles */
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filter-group {
    margin-bottom: 20px;
}

.filter-group h4 {
    margin-bottom: 15px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-check-label {
    color: #666;
    cursor: pointer;
}

.price-range {
    display: flex;
    align-items: center;
    gap: 10px;
}

.price-range input {
    width: 120px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    padding: 20px 0;
}

.product-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    padding-top: 100%;
    overflow: hidden;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-info {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.product-name {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 8px;
    line-height: 1.4;
}

.product-name a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-name a:hover {
    color: #4CAF50;
}

.product-category {
    color: #666;
    font-size: 14px;
    margin-bottom: 12px;
}

.product-price {
    font-size: 20px;
    font-weight: 600;
    color: #4CAF50;
    margin-bottom: 15px;
}

.btn-primary {
    background-color: #4CAF50;
    border-color: #4CAF50;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #45a049;
    border-color: #45a049;
    transform: translateY(-2px);
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 0 10px;
}

.products-header h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.sort-options {
    width: 200px;
}

.sort-options select {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    width: 100%;
    color: #333;
}

.product-actions {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.product-card:hover .product-actions {
    opacity: 1;
    transform: translateY(0);
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #000;
    color: white;
}

@media (max-width: 991px) {
    .banner-section {
        height: 40vh;
    }
    
    .banner-title {
        font-size: 2rem;
    }
    
    .banner-subtitle {
        font-size: 1rem;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-name {
        font-size: 14px;
    }
    
    .product-price {
        font-size: 18px;
    }
}
</style>

<script>
function addToCart(productId) {
    fetch('/shoppingcart/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
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
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.textContent = message;
    
    // Style for notification
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

// Apply filters function
function applyFilters() {
    const selectedCategories = Array.from(document.querySelectorAll('.form-check-input:checked'))
        .map(checkbox => checkbox.value);
    
    const priceFrom = document.getElementById('priceFrom').value;
    const priceTo = document.getElementById('priceTo').value;
    const sort = document.getElementById('sortSelect').value;
    
    const params = new URLSearchParams();
    
    if (selectedCategories.length > 0) {
        params.append('categories', selectedCategories.join(','));
    }
    
    if (priceFrom) {
        params.append('price_from', priceFrom);
    }
    
    if (priceTo) {
        params.append('price_to', priceTo);
    }
    
    if (sort) {
        params.append('sort', sort);
    }
    
    window.location.href = `/shoppingcart/products?${params.toString()}`;
}

// Add keypress event for price inputs
document.querySelectorAll('.price-range input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
});

// Add this to your CSS
const style = document.createElement('style');
style.textContent = `
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

.notification {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
`;
document.head.appendChild(style);
</script> 