<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$product_id) {
    header('Location: /shoppingcart/products');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: /shoppingcart/products');
        exit;
    }

    // Lấy sản phẩm liên quan (cùng danh mục)
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? 
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-detail {
            padding: 2rem 0;
        }

        .product-image {
            position: relative;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .product-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .product-info {
            padding: 1rem 0;
        }

        .product-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .rating-stars {
            color: #ee4d2d;
        }

        .rating-count {
            color: #666;
            border-left: 1px solid #ccc;
            padding-left: 1rem;
        }

        .sold-count {
            color: #666;
            border-left: 1px solid #ccc;
            padding-left: 1rem;
        }

        .product-price {
            background-color: #fafafa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .current-price {
            color: #ee4d2d;
            font-size: 1.8rem;
            font-weight: 500;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 1rem;
            margin-left: 0.5rem;
        }

        .favorite-label {
            display: inline-block;
            background-color: #ee4d2d;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 2px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .quantity-selector {
            margin-bottom: 1.5rem;
        }

        .quantity-label {
            color: #757575;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            max-width: 120px;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ccc;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .quantity-input {
            width: 50px;
            height: 32px;
            border: 1px solid #ccc;
            border-left: none;
            border-right: none;
            text-align: center;
        }

        .stock-info {
            color: #666;
            font-size: 0.85rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-add-to-cart {
            flex: 1;
            background: #ffeee8;
            color: #ee4d2d;
            border: 1px solid #ee4d2d;
            padding: 0.75rem 1rem;
            border-radius: 2px;
        }

        .btn-buy-now {
            flex: 1;
            background: #ee4d2d;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 2px;
        }

        .product-meta {
            padding: 1rem 0;
            border-top: 1px solid #eee;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .stock-status {
            display: inline-block;
            padding: 0.25rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .in-stock {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .out-of-stock {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Related Products Section */
        .related-products {
            padding: 3rem 0;
            background-color: #f8f9fa;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }

        .related-product-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .related-product-card:hover {
            transform: translateY(-5px);
        }

        .related-product-image {
            position: relative;
            padding-top: 100%;
        }

        .related-product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-product-info {
            padding: 1rem;
        }

        .related-product-title {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .related-product-price {
            font-weight: 600;
            color: #333;
        }

        /* Breadcrumb */
        .custom-breadcrumb {
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-item a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #333;
        }

        .breadcrumb-item.active {
            color: #333;
        }

        /* Shop Vouchers */
        .shop-vouchers {
            margin-bottom: 1rem;
        }

        .voucher-title {
            color: #757575;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .voucher-list .btn {
            border: 1px solid #ee4d2d;
            color: #ee4d2d;
            background-color: #fff;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        /* Shipping Info */
        .shipping-info {
            margin-bottom: 1rem;
        }

        .shipping-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            color: #222;
        }

        .shipping-icon {
            color: #666;
            margin-right: 0.5rem;
            min-width: 20px;
        }

        .shipping-text {
            flex: 1;
        }

        .shipping-detail {
            color: #00bfa5;
            font-size: 0.85rem;
        }

        /* Product Variations */
        .variation-group {
            margin-bottom: 1rem;
        }

        .variation-label {
            color: #757575;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .variation-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .variation-option {
            border: 1px solid #ccc;
            padding: 0.5rem 1rem;
            border-radius: 2px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .variation-option.active {
            border-color: #ee4d2d;
            color: #ee4d2d;
        }

        .social-share {
            margin-top: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .share-label {
            color: #757575;
            font-size: 0.9rem;
        }

        .share-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .share-button {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }

        .share-facebook {
            background: #3b5998;
        }

        .share-twitter {
            background: #1da1f2;
        }

        .share-pinterest {
            background: #bd081c;
        }

        @media (max-width: 768px) {
            .product-title {
                font-size: 1.5rem;
            }

            .product-price {
                font-size: 1.25rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-add-to-cart,
            .btn-buy-now {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="custom-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/shoppingcart">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="/shoppingcart/products">Sản phẩm</a></li>
                <li class="breadcrumb-item"><a href="/shoppingcart/products?category=<?= urlencode($product['category_name']) ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <!-- Product Detail -->
        <section class="product-detail">
            <div class="row">
                <div class="col-md-6">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                        <?php else: ?>
                            <img src="/shoppingcart/public/images/default-product.jpg" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product-info">

                        <h1 class="product-title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h1>
                        
                        <div class="product-rating">
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>4.9</span>
                            </div>
                            <div class="rating-count">372 Đánh Giá</div>
                            <div class="sold-count">2,1k Đã bán</div>
                        </div>
                        <div class="product-price">
                            <span class="favorite-label">Yêu Thích</span>
                            <span class="current-price">₫<?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <span class="original-price">₫<?= number_format($product['original_price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Shop Vouchers -->
                        <div class="shop-vouchers">
                            <div class="voucher-title">
                                Mã Giảm Giá Của Shop
                                <a href="#" class="float-end">Xem tất cả <i class="fas fa-chevron-right"></i></a>
                            </div>
                            <div class="voucher-list">
                                <button class="btn">Giảm ₫20k</button>
                                <button class="btn">Giảm ₫37k</button>
                                <button class="btn">Giảm ₫36k</button>
                                <button class="btn">Giảm 12%</button>
                            </div>
                        </div>

                        <!-- Shipping Info -->
                        <div class="shipping-info">
                            <div class="shipping-item">
                                <i class="fas fa-truck shipping-icon"></i>
                                <div class="shipping-text">
                                    <div>Nhận từ 24 Th04 - 25 Th04</div>
                                    <div class="shipping-detail">Miễn phí vận chuyển</div>
                                    <div class="shipping-detail">Tặng Voucher ₫15.000 nếu đơn giao sau thời gian trên.</div>
                                </div>
                            </div>
                            <div class="shipping-item">
                                <i class="fas fa-shield-alt shipping-icon"></i>
                                <div class="shipping-text">
                                    Trả hàng miễn phí 15 ngày · Bảo hiểm Thời trang
                                </div>
                            </div>
                        </div>

                        <!-- Product Variations -->
                        <div class="variation-group">
                            <div class="variation-label">Màu Sắc</div>
                            <div class="variation-options">
                                <button class="variation-option active">Trắng</button>
                                <button class="variation-option">Be</button>
                                <button class="variation-option">Đen</button>
                            </div>
                        </div>

                        <div class="variation-group">
                            <div class="variation-label">Size</div>
                            <div class="variation-options">
                                <button class="variation-option">M</button>
                                <button class="variation-option active">L</button>
                                <button class="variation-option">XL</button>
                            </div>
                        </div>

                        <div class="quantity-selector">
                            <div class="variation-label">Số Lượng</div>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="decrementQuantity()">-</button>
                                <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?= $product['quantity'] ?>">
                                <button class="quantity-btn" onclick="incrementQuantity()">+</button>
                            </div>
                            <span class="stock-info"><?= $product['quantity'] ?> sản phẩm có sẵn</span>
                        </div>

                        
                        <div class="action-buttons">
                            <button class="btn-add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm Vào Giỏ Hàng
                            </button>
                            <button class="btn-buy-now" onclick="buyNow(<?= $product['id'] ?>)">
                                Mua Ngay
                            </button>
                        </div>
                        <div class="social-share">
                            <span class="share-label">Chia sẻ:</span>
                            <div class="share-buttons">
                                <a href="#" class="share-button share-facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="share-button share-twitter"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="share-button share-pinterest"><i class="fab fa-pinterest-p"></i></a>
                            </div>
                        </div>
                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fas fa-box"></i>
                                <span>Tình trạng: </span>
                                <?php if ($product['quantity'] > 0): ?>
                                    <span class="stock-status in-stock">Còn hàng (<?= $product['quantity'] ?>)</span>
                                <?php else: ?>
                                    <span class="stock-status out-of-stock">Hết hàng</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <section class="related-products">
            <h2 class="section-title">Sản phẩm liên quan</h2>
            <div class="row">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="col-md-3 col-6 mb-4">
                        <a href="/shoppingcart/products/detail/<?= $relatedProduct['id'] ?>" class="text-decoration-none">
                            <div class="related-product-card">
                                <div class="related-product-image">
                                    <?php if (!empty($relatedProduct['image'])): ?>
                                        <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($relatedProduct['image']) ?>" 
                                             alt="<?= htmlspecialchars($relatedProduct['name']) ?>"
                                             onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                    <?php else: ?>
                                        <img src="/shoppingcart/public/images/default-product.jpg" 
                                             alt="<?= htmlspecialchars($relatedProduct['name']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="related-product-info">
                                    <h3 class="related-product-title">
                                        <?= htmlspecialchars($relatedProduct['name']) ?>
                                    </h3>
                                    <div class="related-product-price">
                                        <?= number_format($relatedProduct['price'], 0, ',', '.') ?>₫
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        function addToCart(productId) {
            const quantity = document.getElementById('quantity').value;
            fetch('/shoppingcart/app/api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
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

        function buyNow(productId) {
            const quantity = document.getElementById('quantity').value;
            addToCart(productId);
            setTimeout(() => {
                window.location.href = '/shoppingcart/cart';
            }, 1000);
        }

        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        function incrementQuantity() {
            const input = document.getElementById('quantity');
            const maxQuantity = <?= $product['quantity'] ?>;
            let value = parseInt(input.value);
            if (value < maxQuantity) {
                input.value = value + 1;
            }
        }

        function decrementQuantity() {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        }
    </script>
</body>
</html> 