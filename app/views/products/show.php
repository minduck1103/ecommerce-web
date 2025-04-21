<?php $this->partial('header'); ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-6">
            <img src="/shoppingcart/public/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                 class="img-fluid" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-muted">Category: <?= htmlspecialchars($product['category_name']) ?></p>
            <h3 class="text-primary">$<?= number_format($product['price'], 2) ?></h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
            
            <form action="/shoppingcart/cart/add" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= $product['quantity'] ?>">
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add to Cart</button>
            </form>
            
            <a href="/shoppingcart/products" class="btn btn-secondary">Back to Products</a>
        </div>
    </div>
</div>

<?php $this->partial('footer'); ?> 