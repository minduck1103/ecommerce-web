<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND c.slug = ?";
    $params[] = $category;
}

if ($price) {
    list($min, $max) = explode('-', $price);
    $query .= " AND p.price BETWEEN ? AND ?";
    $params[] = $min;
    $params[] = $max;
}

if ($status !== 'all') {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

// Add sorting
switch ($sort) {
    case 'price-asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY p.id DESC"; // Replace with actual popularity metric
        break;
    default: // newest
        $query .= " ORDER BY p.created_at DESC";
}

// Get categories for filter
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get products
$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
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
                    <?php foreach ($categories as $cat): ?>
                    <a href="products.php?category=<?php echo $cat['slug']; ?>"><?php echo $cat['name']; ?></a>
                    <?php endforeach; ?>
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

    <div class="container-fluid py-5">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Bộ lọc tìm kiếm</h5>
                        <form id="filterForm" method="GET">
                            <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Danh mục</label>
                                <select class="form-select" name="category" id="categoryFilter">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['slug']; ?>" <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Khoảng giá</label>
                                <select class="form-select" name="price" id="priceFilter">
                                    <option value="">Tất cả giá</option>
                                    <option value="0-100000" <?php echo $price === '0-100000' ? 'selected' : ''; ?>>Dưới 100.000đ</option>
                                    <option value="100000-200000" <?php echo $price === '100000-200000' ? 'selected' : ''; ?>>100.000đ - 200.000đ</option>
                                    <option value="200000-500000" <?php echo $price === '200000-500000' ? 'selected' : ''; ?>>200.000đ - 500.000đ</option>
                                    <option value="500000-999999999" <?php echo $price === '500000-999999999' ? 'selected' : ''; ?>>Trên 500.000đ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sắp xếp theo</label>
                                <select class="form-select" name="sort" id="sortFilter">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                    <option value="price-asc" <?php echo $sort === 'price-asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                    <option value="price-desc" <?php echo $sort === 'price-desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status" id="statusFilter">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="in-stock" <?php echo $status === 'in-stock' ? 'selected' : ''; ?>>Còn hàng</option>
                                    <option value="out-of-stock" <?php echo $status === 'out-of-stock' ? 'selected' : ''; ?>>Hết hàng</option>
                                    <option value="sale" <?php echo $status === 'sale' ? 'selected' : ''; ?>>Đang giảm giá</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Áp dụng bộ lọc</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <h2 class="mb-4">Tất cả sản phẩm</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <?php if ($product['status'] == 0): ?>
                                    <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 m-2 rounded">
                                        Hết hàng
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-danger fw-bold"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</p>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <p class="card-text">
                                    <small class="text-muted text-decoration-line-through">
                                        <?php echo number_format($product['original_price'], 0, ',', '.'); ?>đ
                                    </small>
                                    <span class="text-danger ms-2">
                                        -<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%
                                    </span>
                                </p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Chi tiết
                                    </a>
                                    <?php if ($product['status'] == 1): ?>
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart me-1"></i> Thêm vào giỏ
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-shopping-cart me-1"></i> Hết hàng
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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

        // Handle add to cart
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                fetch('ajax/add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        document.querySelector('.badge').textContent = data.cartCount;
                        alert('Đã thêm sản phẩm vào giỏ hàng');
                    } else {
                        if (data.requireLogin) {
                            // Store current URL in session storage
                            sessionStorage.setItem('redirectAfterLogin', window.location.href);
                            // Redirect to login page
                            window.location.href = 'login.php';
                        } else {
                            alert(data.message || 'Có lỗi xảy ra');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
            });
        });
    </script>
</body>
</html> 