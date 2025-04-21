<!-- Banner Section -->
<section class="banner-section">
    <div class="banner-container">
        <img src="/shoppingcart/public/images/banner-fashion.jpg" alt="Fashion Banner" class="banner-image">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <h1 class="banner-title">Bộ sưu tập mới</h1>
            <p class="banner-subtitle">Khám phá những sản phẩm mới nhất của chúng tôi</p>
            <a href="/shoppingcart/products" class="banner-button">Xem sản phẩm</a>
        </div>
    </div>
</section>

<!-- Product Categories -->
<div class="container py-5">
    <div class="category-tabs">
        <button class="tab-btn active" data-category="best-seller">BEST SELLER</button>
        <button class="tab-btn" data-category="new-arrival">NEW ARRIVAL</button>
    </div>

    <!-- Product Grid - Best Sellers -->
    <div class="product-grid" id="best-seller-grid">
        <?php foreach ($bestSellers as $product): ?>
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

    <!-- Product Grid - New Arrivals -->
    <div class="product-grid" id="new-arrival-grid" style="display: none;">
        <?php foreach ($newArrivals as $product): ?>
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

    <div class="text-center mt-4">
        <a href="/shoppingcart/products" class="view-all-btn">Xem tất cả</a>
    </div>
</div>

<!-- Shop Overview Section -->
<section class="shop-overview">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="overview-image">
                    <img src="/shoppingcart/public/images/about-us.jpg" alt="About Us" class="img-fluid rounded shadow">
                </div>
            </div>
            <div class="col-md-6">
                <div class="overview-content">
                    <h2>Về chúng tôi</h2>
                    <p>Chào mừng bạn đến với cửa hàng thời trang của chúng tôi! Chúng tôi tự hào mang đến những sản phẩm thời trang chất lượng cao với thiết kế độc đáo và phong cách hiện đại.</p>
                    <p>Với hơn 5 năm kinh nghiệm trong ngành thời trang, chúng tôi luôn đặt sự hài lòng của khách hàng lên hàng đầu. Mỗi sản phẩm được chọn lọc kỹ càng, đảm bảo chất lượng và xu hướng thời trang mới nhất.</p>
                    <a href="/shoppingcart/about" class="btn-learn-more">Tìm hiểu thêm</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shopping Benefits Section -->
<section class="shopping-benefits">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="benefits-content">
                    <h2>Ưu đãi mua sắm</h2>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> Miễn phí vận chuyển cho đơn hàng từ 500.000đ</li>
                        <li><i class="fas fa-check"></i> Đổi trả miễn phí trong vòng 30 ngày</li>
                        <li><i class="fas fa-check"></i> Tích điểm thành viên - Nhận ưu đãi hấp dẫn</li>
                        <li><i class="fas fa-check"></i> Quà tặng đặc biệt cho thành viên VIP</li>
                    </ul>
                    <a href="/shoppingcart/membership" class="btn-join-now">Tham gia ngay</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="benefits-image">
                    <img src="/shoppingcart/public/images/shopping-benefits.jpg" alt="Shopping Benefits" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Banner Section */
.banner-section {
    margin-top: -80px;
    height: 100vh;
    position: relative;
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

.banner-button {
    display: inline-block;
    padding: 1rem 3rem;
    background-color: white;
    color: black;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.banner-button:hover {
    background-color: black;
    color: white;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.product-card {
    position: relative;
}

.product-image {
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
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

.product-info {
    padding: 1rem 0;
    text-align: center;
}

.product-name {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.product-rating {
    color: #ffd700;
    margin-bottom: 0.5rem;
}

.product-price {
    font-weight: 600;
    color: #000;
}

/* Category Tabs */
.category-tabs {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 3rem;
}

.tab-btn {
    font-size: 1.2rem;
    font-weight: 600;
    background: none;
    border: none;
    padding: 0.5rem 1rem;
    position: relative;
    cursor: pointer;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #000;
}

/* Shop Overview Section */
.shop-overview {
    padding: 5rem 0;
    background: #f8f9fa;
}

.overview-image img {
    width: 100%;
    transition: transform 0.3s ease;
}

.overview-image:hover img {
    transform: scale(1.02);
}

.overview-content {
    padding: 2rem;
}

.overview-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.overview-content p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #666;
    margin-bottom: 1rem;
}

.btn-learn-more {
    display: inline-block;
    padding: 1rem 2rem;
    background: #000;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-learn-more:hover {
    background: #333;
    color: white;
}

/* Shopping Benefits Section */
.shopping-benefits {
    padding: 5rem 0;
}

.benefits-content {
    padding: 2rem;
}

.benefits-content h2 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
}

.benefits-list {
    list-style: none;
    padding: 0;
}

.benefits-list li {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.benefits-list li i {
    color: #28a745;
    margin-right: 1rem;
}

.benefits-image img {
    width: 100%;
    transition: transform 0.3s ease;
}

.benefits-image:hover img {
    transform: scale(1.02);
}

.btn-join-now {
    display: inline-block;
    padding: 1rem 2rem;
    background: #000;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 2rem;
    transition: all 0.3s ease;
}

.btn-join-now:hover {
    background: #333;
    color: white;
}

/* View All Button */
.view-all-btn {
    display: inline-block;
    padding: 1rem 3rem;
    background: #000;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.view-all-btn:hover {
    background: #333;
    color: white;
}

@media (max-width: 768px) {
    .banner-title {
        font-size: 2rem;
    }
    
    .banner-subtitle {
        font-size: 1rem;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .overview-image img,
    .benefits-image img {
        height: 300px;
    }
    
    .overview-content,
    .benefits-content {
        padding: 1rem;
    }
}
</style>

<script>
// Tab switching functionality
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');
        
        // Hide all grids
        document.getElementById('best-seller-grid').style.display = 'none';
        document.getElementById('new-arrival-grid').style.display = 'none';
        
        // Show selected grid
        const category = button.getAttribute('data-category');
        document.getElementById(category + '-grid').style.display = 'grid';
    });
});

function addToCart(productId) {
    <?php if (!isset($_SESSION['user_id'])): ?>
    window.location.href = '/shoppingcart/auth/login';
    alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    <?php else: ?>
    fetch('/shoppingcart/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã thêm sản phẩm vào giỏ hàng');
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => console.error('Error:', error));
    <?php endif; ?>
}

function addToWishlist(productId) {
    <?php if (!isset($_SESSION['user_id'])): ?>
    window.location.href = '/shoppingcart/auth/login';
    alert('Vui lòng đăng nhập để thêm sản phẩm vào danh sách yêu thích');
    <?php else: ?>
    // Add to wishlist logic here
    <?php endif; ?>
}
</script> 