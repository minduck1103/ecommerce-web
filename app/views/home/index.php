<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

$database = new Database();
$conn = $database->getConnection();

// Debug connection
if ($conn) {
    echo "<!-- Database connected successfully -->";
} else {
    echo "<!-- Database connection failed -->";
}

// Lấy sản phẩm bán chạy nhất
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LIMIT 8
    ");
    $stmt->execute();
    $bestSellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<!-- Found " . count($bestSellers) . " best sellers -->";
} catch (PDOException $e) {
    echo "<!-- Error in best sellers query: " . $e->getMessage() . " -->";
    $bestSellers = [];
}

// Lấy sản phẩm mới nhất
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $newArrivals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<!-- Found " . count($newArrivals) . " new arrivals -->";
} catch (PDOException $e) {
    echo "<!-- Error in new arrivals query: " . $e->getMessage() . " -->";
    $newArrivals = [];
}

// Debug data
echo "<!-- Best Sellers Data: " . json_encode($bestSellers) . " -->";
echo "<!-- New Arrivals Data: " . json_encode($newArrivals) . " -->";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Banner Section */
        .banner-section {
            margin-top: -80px;
            height: 80vh;
            position: relative;
            margin-bottom: 2rem;
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
            object-position: center;
            display: block;
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.3));
            z-index: 1;
        }

        .banner-content {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            z-index: 2;
        }

        .banner-button-container {
            text-align: center;
        }

        .banner-button {
            display: inline-block;
            padding: 1rem 3rem;
            background-color: #888;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .banner-button:hover {
            background-color: #999;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        /* Category Tabs */
        .category-tabs {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .tab-btn {
            padding: 0.5rem 2rem;
            border: none;
            background: none;
            font-size: 1.1rem;
            font-weight: 600;
            color: #666;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #888;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .tab-btn.active {
            color: #333;
        }

        .tab-btn.active::after {
            transform: scaleX(1);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        /* Product Card */
        .product-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-actions {
            position: absolute;
            bottom: 1rem;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 1rem;
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
            background: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .action-btn:hover {
            background: #888;
            color: #fff;
            transform: translateY(-2px);
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .product-name a {
            color: #333;
            text-decoration: none;
        }

        .product-name a:hover {
            color: #888;
        }

        .product-rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        /* View All Button */
        .view-all-btn {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background-color: #888;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .view-all-btn:hover {
            background-color: #666;
            color: white;
            transform: translateY(-2px);
        }

        /* Shop Overview Section */
        .shop-overview {
            padding: 5rem 0;
            background-color: #f8f9fa;
        }

        .overview-content {
            padding: 2rem;
        }

        .overview-content h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .overview-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .btn-learn-more {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: #888;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-learn-more:hover {
            background-color: #666;
            color: white;
            transform: translateY(-2px);
        }

        /* Shopping Benefits Section */
        .shopping-benefits {
            padding: 5rem 0;
        }

        .benefits-content {
            padding: 2rem;
        }

        .benefits-content h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            margin-bottom: 1rem;
            color: #666;
            display: flex;
            align-items: center;
        }

        .benefits-list li i {
            color: #28a745;
            margin-right: 1rem;
        }

        .btn-join-now {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: #888;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .btn-join-now:hover {
            background-color: #666;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Banner Section -->
<section class="banner-section">
    <div class="banner-container">
        <img src="/shoppingcart/public/images/banner-fashion.jpg" alt="Fashion Banner" class="banner-image">
        <div class="banner-overlay"></div>
        <div class="banner-content">
                <div class="banner-button-container">
            <a href="/shoppingcart/products" class="banner-button">Xem sản phẩm</a>
                </div>
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
            <?php if (!empty($bestSellers)): ?>
        <?php foreach ($bestSellers as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                <?php else: ?>
                                    <img src="/shoppingcart/public/images/default-product.jpg" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php endif; ?>
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
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                    </div>
                <div class="product-price">
                    <?= number_format($product['price'], 0, ',', '.') ?>₫
                </div>
            </div>
                        </div>
        <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center w-100">
                    <p>Không có sản phẩm nào.</p>
                </div>
            <?php endif; ?>
                    </div>

    <!-- Product Grid - New Arrivals -->
    <div class="product-grid" id="new-arrival-grid" style="display: none;">
            <?php if (!empty($newArrivals)): ?>
        <?php foreach ($newArrivals as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                <?php else: ?>
                                    <img src="/shoppingcart/public/images/default-product.jpg" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php endif; ?>
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
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                        </div>
                <div class="product-price">
                    <?= number_format($product['price'], 0, ',', '.') ?>₫
                </div>
            </div>
        </div>
        <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center w-100">
                    <p>Không có sản phẩm nào.</p>
                </div>
            <?php endif; ?>
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

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                document.getElementById(`${category}-grid`).style.display = 'grid';
    });
});

        // Add to cart functionality
function addToCart(productId) {
            fetch('/shoppingcart/app/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    // Hiển thị toast thông báo
                    const toast = document.createElement('div');
                    toast.className = 'toast position-fixed bottom-0 end-0 m-3';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Thông báo</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Sản phẩm đã được thêm vào giỏ hàng
                        </div>
                    `;
                    document.body.appendChild(toast);
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    // Cập nhật số lượng trong giỏ hàng
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }

                    // Tự động xóa toast sau khi ẩn
                    toast.addEventListener('hidden.bs.toast', () => {
                        toast.remove();
                    });
        } else {
                    // Hiển thị toast lỗi
                    const toast = document.createElement('div');
                    toast.className = 'toast position-fixed bottom-0 end-0 m-3';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `
                        <div class="toast-header bg-danger text-white">
                            <strong class="me-auto">Lỗi</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${data.message || 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng'}
                        </div>
                    `;
                    document.body.appendChild(toast);
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    // Tự động xóa toast sau khi ẩn
                    toast.addEventListener('hidden.bs.toast', () => {
                        toast.remove();
                    });
        }
    })
            .catch(error => {
                console.error('Error:', error);
                // Hiển thị toast lỗi
                const toast = document.createElement('div');
                toast.className = 'toast position-fixed bottom-0 end-0 m-3';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.innerHTML = `
                    <div class="toast-header bg-danger text-white">
                        <strong class="me-auto">Lỗi</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng
                    </div>
                `;
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();

                // Tự động xóa toast sau khi ẩn
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            });
        }

        // Add to wishlist functionality
function addToWishlist(productId) {
            fetch('/shoppingcart/api/wishlist/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sản phẩm đã được thêm vào danh sách yêu thích');
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi thêm sản phẩm vào danh sách yêu thích');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm sản phẩm vào danh sách yêu thích');
            });
}
</script> 
</body>
</html> 