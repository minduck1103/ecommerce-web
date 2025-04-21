<?php foreach ($products as $product): ?>
<div class="product-card">
    <div class="product-image">
        <a href="/shoppingcart/products/detail/<?= $product['id'] ?>">
            <img src="/shoppingcart/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </a>
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
        <button class="btn btn-primary w-100" onclick="addToCart(<?= $product['id'] ?>)">
            Thêm vào giỏ
        </button>
    </div>
</div>
<?php endforeach; ?> 