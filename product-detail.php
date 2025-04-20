<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product not found, redirect to products page
if (!$product) {
    header('Location: products.php');
    exit;
}

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll();

// If no additional images, use main product image
if (empty($images)) {
    $images = [['image_path' => $product['image']]];
}

// Get related products
$stmt = $conn->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopCart</title>
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
        .product-gallery {
            position: relative;
        }
        .thumbnail-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: #0d6efd;
        }
        .size-option {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-right: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .size-option:hover, .size-option.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .color-option {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .color-option:hover, .color-option.active {
            border-color: #0d6efd;
        }
        .quantity-input {
            width: 100px;
        }
        .related-product {
            transition: transform 0.3s;
        }
        .related-product:hover {
            transform: translateY(-5px);
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

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Tìm kiếm sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="searchForm">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="Nhập tên sản phẩm...">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Danh mục</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="ao-thun">Áo thun</option>
                                    <option value="ao-sweater">Áo sweater</option>
                                    <option value="ao-khoac">Áo khoác</option>
                                    <option value="quan-dai">Quần dài</option>
                                    <option value="quan-ngan">Quần ngắn</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Khoảng giá</label>
                                <select class="form-select" id="priceFilter">
                                    <option value="">Tất cả giá</option>
                                    <option value="0-100000">Dưới 100.000đ</option>
                                    <option value="100000-200000">100.000đ - 200.000đ</option>
                                    <option value="200000-500000">200.000đ - 500.000đ</option>
                                    <option value="500000-999999999">Trên 500.000đ</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Sắp xếp theo</label>
                                <select class="form-select" id="sortFilter">
                                    <option value="newest">Mới nhất</option>
                                    <option value="price-asc">Giá tăng dần</option>
                                    <option value="price-desc">Giá giảm dần</option>
                                    <option value="popular">Bán chạy nhất</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="all">Tất cả</option>
                                    <option value="in-stock">Còn hàng</option>
                                    <option value="out-of-stock">Hết hàng</option>
                                    <option value="sale">Đang giảm giá</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="searchButton">Tìm kiếm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Product Gallery -->
            <div class="col-md-6 mb-4">
                <div class="product-gallery">
                    <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainImage">
                    <div class="thumbnail-container">
                        <?php foreach ($images as $index => $image): ?>
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             alt="Thumbnail <?php echo $index + 1; ?>"
                             onclick="changeMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="mb-3">
                    <span class="text-danger h4"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                    <?php if ($product['original_price']): ?>
                    <span class="text-decoration-line-through text-muted ms-2"><?php echo number_format($product['original_price'], 0, ',', '.'); ?>đ</span>
                    <?php endif; ?>
                    <?php if ($product['status'] === 'sale'): ?>
                    <span class="badge bg-danger ms-2">-<?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>%</span>
                    <?php endif; ?>
                </div>
                <p class="mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <!-- Size Selection -->
                <div class="mb-4">
                    <h5>Kích thước</h5>
                    <div>
                        <span class="size-option">S</span>
                        <span class="size-option active">M</span>
                        <span class="size-option">L</span>
                        <span class="size-option">XL</span>
                        <span class="size-option">XXL</span>
                    </div>
                </div>
                
                <!-- Color Selection -->
                <div class="mb-4">
                    <h5>Màu sắc</h5>
                    <div>
                        <span class="color-option active" style="background-color: #000;"></span>
                        <span class="color-option" style="background-color: #fff; border: 1px solid #ddd;"></span>
                        <span class="color-option" style="background-color: #808080;"></span>
                        <span class="color-option" style="background-color: #0000FF;"></span>
                    </div>
                </div>
                
                <!-- Quantity -->
                <div class="mb-4">
                    <h5>Số lượng</h5>
                    <div class="input-group quantity-input">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                        <input type="text" class="form-control text-center" value="1" id="quantityInput">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
                    </div>
                </div>
                
                <!-- Add to Cart -->
                <div class="d-grid gap-2 d-md-flex mb-4">
                    <button class="btn btn-primary flex-grow-1" onclick="addToCart()">
                        <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-outline-danger">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
                
                <!-- Product Details -->
                <div class="accordion" id="productAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Mô tả sản phẩm
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#productAccordion">
                            <div class="accordion-body">
                                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                <ul>
                                    <li>Chất liệu: Cotton 100%</li>
                                    <li>Form: Regular fit</li>
                                    <li>Cổ: Cổ tròn</li>
                                    <li>Tay: Tay ngắn</li>
                                    <li>Họa tiết: Trơn</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Thông tin vận chuyển
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#productAccordion">
                            <div class="accordion-body">
                                <p>Chúng tôi giao hàng toàn quốc trong vòng 2-5 ngày làm việc. Phí vận chuyển từ 30.000đ tùy theo địa điểm.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Chính sách đổi trả
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#productAccordion">
                            <div class="accordion-body">
                                <p>Chúng tôi chấp nhận đổi trả trong vòng 30 ngày kể từ ngày nhận hàng nếu sản phẩm còn nguyên vẹn và đầy đủ phụ kiện.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="mt-5">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($related_products as $related): ?>
                <div class="col">
                    <div class="card h-100 related-product">
                        <img src="<?php echo htmlspecialchars($related['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                            <p class="card-text text-danger"><?php echo number_format($related['price'], 0, ',', '.'); ?>đ</p>
                            <div class="d-flex justify-content-between">
                                <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> Xem
                                </a>
                                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $related['id']; ?>">
                                    <i class="fas fa-shopping-cart me-1"></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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

        // Change main image
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.src === src) {
                    thumb.classList.add('active');
                }
            });
        }

        // Update quantity
        function updateQuantity(change) {
            const input = document.getElementById('quantityInput');
            let value = parseInt(input.value) + change;
            if (value < 1) value = 1;
            input.value = value;
        }

        // Add to cart
        function addToCart() {
            const quantity = parseInt(document.getElementById('quantityInput').value);
            fetch('ajax/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=<?php echo $product_id; ?>&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.querySelector('.badge').textContent = data.cartCount;
                    alert('Đã thêm sản phẩm vào giỏ hàng');
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        // Handle add to cart for related products
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
                        alert(data.message || 'Có lỗi xảy ra');
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