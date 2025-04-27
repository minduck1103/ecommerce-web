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

// Xử lý tìm kiếm
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchFilter = '';
if (!empty($searchQuery)) {
    $searchFilter = "WHERE p.name LIKE :search";
}

// Xử lý lọc theo danh mục
$categoryFilter = '';
if (isset($_GET['category'])) {
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$_GET['category']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category) {
        $categoryFilter = empty($searchFilter) ? 
            "WHERE p.category_id = " . $category['id'] : 
            " AND p.category_id = " . $category['id'];
    }
}

// Xử lý sắp xếp
$sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$orderBy = 'ORDER BY p.created_at DESC'; // Mặc định sắp xếp theo mới nhất

switch ($sortOption) {
    case 'price-asc':
        $orderBy = 'ORDER BY p.price ASC';
        break;
    case 'price-desc':
        $orderBy = 'ORDER BY p.price DESC';
        break;
    case 'name-asc':
        $orderBy = 'ORDER BY p.name ASC';
        break;
    case 'name-desc':
        $orderBy = 'ORDER BY p.name DESC';
        break;
}

// Lấy danh sách sản phẩm
try {
    $query = "
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $searchFilter
        $categoryFilter
        $orderBy
    ";
    $stmt = $conn->prepare($query);
    if (!empty($searchQuery)) {
        $stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
    }
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
        .banner-section {
            margin-top: -80px;
            height: 50vh; 
            position: relative;
            margin-bottom: 2rem;
            background-color: #fff;
            width: 100%;
            overflow: hidden;
            max-height: 600px;
            max-width: 1320px;
            margin-left: auto;
            margin-right: auto;
        }

        .banner-container {
            width: 100%;
            max-width: 1320px;
            margin: 0 auto;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .banner-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            opacity: 0.9;
            transform: scale(1.02);
            transition: transform 6s ease;
        }

        .banner-container:hover .banner-image {
            transform: scale(1.1);
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.3) 0%,
                rgba(0, 0, 0, 0.5) 100%
            );
            z-index: 1;
        }

        .banner-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 2;
            width: 100%;
            padding: 0 2rem;
            color: white;
        }

        .banner-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif
        }

        .banner-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif
        }

        .banner-button {
            display: inline-block;
            padding: 1rem 2.5rem;
            background-color: white;
            color: #333;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .banner-button:hover {
            background-color: #333;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Search Section */
        .search-section {
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-button {
            padding: 0.5rem 2rem;
            background-color: #888;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            background-color: #666;
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
                height: 50vh;
            }

            .banner-title {
                font-size: 2.5rem;
            }

            .banner-subtitle {
                font-size: 1.1rem;
            }

            .banner-button {
                padding: 0.875rem 2rem;
                font-size: 1rem;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }

        @media (max-width: 480px) {
            .banner-section {
                height: 40vh;
            }

            .banner-title {
                font-size: 2rem;
                letter-spacing: 1px;
            }

            .banner-subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .banner-button {
                padding: 0.75rem 1.75rem;
                font-size: 0.9rem;
            }

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

    <div class="banner-section">
        <div class="banner-container">
            <img src="/shoppingcart/public/images/banner-products.jpg" alt="Banner" class="banner-image">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <h1 class="banner-title">
                    <?php if (!empty($searchQuery)): ?>
                        <?php echo htmlspecialchars($searchQuery); ?>
                    <?php else: ?>
                        Bộ sưu tập mùa hè 2025
                    <?php endif; ?>
                </h1>
                <p class="banner-subtitle">
                    <?php if (!empty($searchQuery)): ?>
                        Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($searchQuery); ?>"
                    <?php else: ?>
                        Hàng ngàn sản phẩm đang chờ được chọn lựa
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Filter Section -->
            <div class="col-lg-3">
                <div class="filter-section">
                    <div class="filter-header">
                        <h3 class="filter-title">Bộ lọc</h3>
                        <button type="button" class="reset-filter" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Đặt lại
                        </button>
                    </div>

                    <div class="category-list">
                        <a href="/shoppingcart/products" 
                           class="category-item <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                            Tất cả sản phẩm
                            <span class="product-count"><?php echo array_sum(array_column($categories, 'product_count')); ?></span>
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="/shoppingcart/products?category=<?php echo $category['slug']; ?>" 
                               class="category-item <?php echo (isset($_GET['category']) && $_GET['category'] === $category['slug']) ? 'active' : ''; ?>">
                                <?php echo $category['name']; ?>
                                <span class="product-count"><?php echo $category['product_count']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="products-header">
                    <div class="search-and-title">
                        <h2>Tất cả sản phẩm</h2>
                        <div class="search-box">
                            <input type="text" 
                                   id="searchInput" 
                                   placeholder="Tìm kiếm sản phẩm..."
                                   value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <button onclick="searchProducts()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="sort-options">
                        <select class="form-select" id="sortSelect" onchange="applySort(this.value)">
                            <option value="newest" <?php echo $sortOption === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                            <option value="price-asc" <?php echo $sortOption === 'price-asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                            <option value="price-desc" <?php echo $sortOption === 'price-desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                            <option value="name-asc" <?php echo $sortOption === 'name-asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                            <option value="name-desc" <?php echo $sortOption === 'name-desc' ? 'selected' : ''; ?>>Tên Z-A</option>
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
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <?php if (!empty($searchQuery)): ?>
                                    Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($searchQuery); ?>"
                                <?php else: ?>
                                    Không có sản phẩm nào trong danh mục này
                                <?php endif; ?>
                            </div>
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
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (searchTerm !== '') {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', searchTerm);
                const category = urlParams.get('category');
                let newUrl = '/shoppingcart/products?search=' + encodeURIComponent(searchTerm);
                if (category) {
                    newUrl += '&category=' + encodeURIComponent(category);
                }
                window.location.href = newUrl;
            }
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchProducts();
            }
        });

        function applySort(sortValue) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', sortValue);
            let newUrl = '/shoppingcart/products?' + urlParams.toString();
            window.location.href = newUrl;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort') || 'newest';
            document.getElementById('sortSelect').value = currentSort;
        });

        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        function addToCart(productId) {
            fetch('/shoppingcart/app/api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toast.success(data.message);
                    updateCartCount(data.cart_count);
                } else {
                    Toast.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.error('Có lỗi xảy ra khi thêm vào giỏ hàng');
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
                    Toast.success('Sản phẩm đã được thêm vào danh sách yêu thích');
                } else {
                    Toast.error(data.message || 'Có lỗi xảy ra khi thêm sản phẩm vào danh sách yêu thích');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.error('Có lỗi xảy ra khi thêm sản phẩm vào danh sách yêu thích');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetch('/shoppingcart/app/api/cart/count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html> 