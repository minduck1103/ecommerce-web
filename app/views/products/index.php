<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách danh mục
try {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Xử lý lọc theo danh mục
$categoryFilter = '';
if (isset($_GET['category'])) {
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$_GET['category']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category) {
        $categoryFilter = "WHERE p.category_id = " . $category['id'];
    }
}

// Lấy danh sách sản phẩm
try {
    $query = "
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $categoryFilter
        ORDER BY p.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Banner Section */
        .banner-section {
            margin-top: -80px;
            height: 60vh;
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
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.4));
            z-index: 1;
        }

        .banner-content {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 2;
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }

        .reset-filter {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .reset-filter:hover {
            color: #333;
        }

        /* Category List */
        .category-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #666;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .category-item:hover {
            background-color: #f8f9fa;
            color: #333;
        }

        .category-item.active {
            background-color: #888;
            color: white;
        }

        .product-count {
            font-size: 0.9rem;
            color: inherit;
        }

        /* Price Range */
        .price-range {
            margin-top: 1rem;
        }

        .price-inputs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .price-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .separator {
            color: #666;
        }

        /* Products Header */
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .search-and-title {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-box input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 250px;
        }

        .search-box button {
            padding: 0.5rem 1rem;
            background: #888;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-box button:hover {
            background: #666;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        /* Product Card */
        .product-item {
            position: relative;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: #f8f9fa;
        }

        .product-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img {
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
            z-index: 2;
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
            flex-grow: 1;
            display: flex;
            flex-direction: column;
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

        .product-category {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-price-wrapper {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-section {
            display: flex;
            flex-direction: column;
        }

        .original-price {
            color: #999;
            text-decoration: line-through;
            font-size: 0.9rem;
        }

        .current-price {
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .stock-status {
            font-size: 0.8rem;
        }

        .in-stock-badge {
            color: #28a745;
            background-color: #d4edda;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .out-of-stock-badge {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .sale-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 2;
        }

        /* No Products Message */
        .no-products {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .no-products p {
            color: #666;
            font-size: 1.1rem;
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .search-and-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .products-header {
                flex-direction: column;
                gap: 1rem;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .banner-section {
                height: 40vh;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }

        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-name {
                font-size: 0.9rem;
            }

            .current-price {
                font-size: 1rem;
            }
        }

        /* Toast Styles */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .toast {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease-out;
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
            border-left: 4px solid #2ecc71;
        }

        .toast.error {
            border-left: 4px solid #e74c3c;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

<!-- Banner Section -->
<section class="banner-section">
    <div class="banner-container">
            <img src="/shoppingcart/public/images/banner-products.jpg" alt="Banner" class="banner-image">
                    <div class="banner-overlay"></div>
                    <div class="banner-content">
                        <a href="#products" class="banner-button">Khám phá ngay</a>
        </div>
    </div>
</section>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="filter-section">
                    <div class="filter-header">
                        <h3 class="filter-title">Danh mục</h3>
                        <button onclick="resetFilters()" class="reset-filter" title="Đặt lại bộ lọc">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                    </div>
                
                <!-- Category Filter -->
                    <div class="category-list">
                        <a href="/shoppingcart/products" class="category-item <?= empty($_GET['category']) ? 'active' : '' ?>">
                            Tất cả sản phẩm
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="/shoppingcart/products?category=<?= htmlspecialchars($category['slug']) ?>" 
                           class="category-item <?= isset($_GET['category']) && $_GET['category'] === $category['slug'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            <span class="product-count">(<?= $category['product_count'] ?? 0 ?>)</span>
                        </a>
                        <?php endforeach; ?>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-group">
                    <h4 class="filter-group-title">Khoảng giá</h4>
                    <div class="price-range">
                        <div class="price-inputs">
                                <input type="number" class="price-input input-min" placeholder="Từ" onchange="applyPriceFilter()">
                            <div class="separator">-</div>
                                <input type="number" class="price-input input-max" placeholder="Đến" onchange="applyPriceFilter()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="products-header">
                    <div class="search-and-title">
                <h2>Tất cả sản phẩm</h2>
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
                            <button onclick="searchProducts()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                <div class="sort-options">
                    <select class="form-select" id="sortSelect" onchange="applyFilters()">
                        <option value="newest">Mới nhất</option>
                        <option value="price-asc">Giá tăng dần</option>
                        <option value="price-desc">Giá giảm dần</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <div class="product-card">
                                <!-- Product Image with Hover Effect -->
                                <div class="product-image">
                                    <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 class="product-img"
                                                 onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                        <?php else: ?>
                                            <img src="/shoppingcart/public/images/default-product.jpg" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 class="product-img">
                                        <?php endif; ?>
                                    </a>
                                    
                                    <!-- Quick Action Buttons -->
                                    <div class="product-actions">
                                        <button class="action-btn add-to-cart" 
                                                onclick="addToCart(<?= $product['id'] ?>)" 
                                                title="Thêm vào giỏ hàng">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button class="action-btn add-to-wishlist" 
                                                onclick="addToWishlist(<?= $product['id'] ?>)"
                                                title="Thêm vào yêu thích">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>

                                    <!-- Sale Badge -->
                                    <?php if ($product['status'] === 'sale'): ?>
                                        <span class="sale-badge">Sale</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Product Info -->
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h3>
                                    <div class="product-category">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </div>
                                    <div class="product-price-wrapper">
                                        <div class="price-section">
                                            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                                <span class="original-price">
                                                    <?= number_format($product['original_price'], 0, ',', '.') ?>₫
                                                </span>
                                            <?php endif; ?>
                                            <span class="current-price">
                                                <?= number_format($product['price'], 0, ',', '.') ?>₫
                                            </span>
                                        </div>
                                        <div class="stock-status">
                                            <?php if ($product['quantity'] > 0): ?>
                                                <span class="in-stock-badge">Còn hàng</span>
                                            <?php else: ?>
                                                <span class="out-of-stock-badge">Hết hàng</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Không tìm thấy sản phẩm nào.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <div class="toast-container"></div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetFilters() {
            window.location.href = '/shoppingcart/products';
        }

        function searchProducts() {
            const searchTerm = document.getElementById('searchInput').value;
            // Implement search functionality
        }

        function applyPriceFilter() {
            const minPrice = document.querySelector('.input-min').value;
            const maxPrice = document.querySelector('.input-max').value;
            // Implement price filter
        }

        function applyFilters() {
            const sortValue = document.getElementById('sortSelect').value;
            // Implement sorting
        }

        function showToast(message, type = 'success') {
            const container = document.querySelector('.toast-container') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                <span>${message}</span>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => container.removeChild(toast), 300);
            }, 3000);
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
            return container;
        }

        function fetchCartCount() {
            fetch('/shoppingcart/app/api/cart/count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

function addToCart(productId) {
            fetch('/shoppingcart/app/api/cart/cart.php', {
        method: 'POST',
        headers: {
                    'Content-Type': 'application/json',
        },
        body: JSON.stringify({
                    action: 'add',
                    product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    // Cập nhật số lượng giỏ hàng
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        element.textContent = data.cart_count;
                    });
                    
                    // Hiển thị thông báo thành công
                    showToast('Đã thêm sản phẩm vào giỏ hàng');
        } else {
                    showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
                showToast('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
    });
}

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

        // Fetch cart count when page loads
        document.addEventListener('DOMContentLoaded', fetchCartCount);
</script> 
</body>
</html> 