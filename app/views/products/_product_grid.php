<?php
// Debug information
if (isset($_GET['debug'])) {
    echo '<div style="background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;">';
    echo '<h3>Debug Information</h3>';
    echo '<h4>Products Data:</h4>';
    echo '<pre>';
    var_dump($products);
    echo '</pre>';
    
    echo '<h4>Server Information:</h4>';
    echo '<pre>';
    echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo 'Script Filename: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo 'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n";
    echo '</pre>';
    echo '</div>';
}

if (!empty($products)):
    foreach ($products as $product):
        $imagePath = "/shoppingcart/public/uploads/" . htmlspecialchars($product['image']);
        $imageExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath);
?>
<div class="product-item">
    <div class="product-card">
        <!-- Product Image with Hover Effect -->
        <div class="product-image">
            <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                <?php if (!empty($product['image'])): ?>
                    <img src="<?= $imagePath ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="product-img"
                         onerror="this.onerror=null; this.src='/shoppingcart/public/assets/images/no-image.png';">
                    <?php if (isset($_GET['debug'])): ?>
                    <div style="background: rgba(0,0,0,0.7); color: white; padding: 5px; position: absolute; bottom: 0; left: 0; right: 0; font-size: 12px;">
                        Image: <?= $product['image'] ?><br>
                        Path: <?= $imagePath ?><br>
                        Exists: <?= $imageExists ? 'Yes' : 'No' ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="/shoppingcart/public/assets/images/no-image.png" 
                         alt="No image available"
                         class="product-img">
                    <?php if (isset($_GET['debug'])): ?>
                    <div style="background: rgba(0,0,0,0.7); color: white; padding: 5px; position: absolute; bottom: 0; left: 0; right: 0; font-size: 12px;">
                        No image set for this product
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <h3 class="product-name">
                <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
                    <?= htmlspecialchars($product['name']) ?>
                </a>
            </h3>
            <div class="product-category">
                <?= htmlspecialchars($product['category_name'] ?? '') ?>
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
            <button class="btn btn-primary w-100" onclick="addToCart(<?= $product['id'] ?>)">
                <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    background: white;
    margin-bottom: 20px;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    padding-top: 100%; /* 1:1 Aspect Ratio */
    overflow: hidden;
    background: #f8f9fa;
}

.product-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changed from cover to contain */
    transition: transform 0.3s;
}

.product-card:hover .product-img {
    transform: scale(1.05);
}

.product-info {
    padding: 1rem;
}

.product-name {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    height: 2.4rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-name a {
    color: #333;
    text-decoration: none;
}

.product-name a:hover {
    color: #007bff;
}

.product-category {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.product-price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #dc3545;
    margin-bottom: 1rem;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    transition: all 0.2s;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
}
</style>

<?php else: ?>
    <div class="no-products">
        <p>Không tìm thấy sản phẩm nào.</p>
    </div>
<?php endif; ?> 