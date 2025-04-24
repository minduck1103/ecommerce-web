<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
:root {
    --primary-color: #333;
    --primary-dark: #222;
    --text-muted: #666;
    --border-color: #ddd;
    --background-color: #f8f9fa;
}
</style>

<div class="empty-cart-wrapper">
    <div class="empty-cart-illustration">
        <div class="cart-circle"></div>
        <div class="cart-icon">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>
    
    <div class="empty-cart-info">
        <h1>Hmmm... Giỏ hàng có vẻ trống!</h1>
        <p>Đừng để giỏ hàng của bạn cô đơn. Hãy khám phá những sản phẩm tuyệt vời của chúng tôi.</p>
        
        <div class="action-buttons">
            <a href="/shoppingcart" class="primary-btn">
                <span>Khám phá ngay</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            <a href="/shoppingcart/products/new" class="secondary-btn">
                <i class="fas fa-star me-2"></i>
                <span>Sản phẩm mới</span>
            </a>
        </div>
    </div>

    <div class="featured-section">
        <div class="section-header">
            <h2>Xu hướng mua sắm</h2>
            <p>Những sản phẩm được yêu thích nhất</p>
        </div>
        
        <div class="featured-categories">
            <div class="category-card">
                <i class="fas fa-tshirt"></i>
                <span>Thời trang</span>
            </div>
            <div class="category-card">
                <i class="fas fa-mobile-alt"></i>
                <span>Điện tử</span>
            </div>
            <div class="category-card">
                <i class="fas fa-gem"></i>
                <span>Phụ kiện</span>
            </div>
            <div class="category-card">
                <i class="fas fa-home"></i>
                <span>Nội thất</span>
            </div>
        </div>
    </div>
</div>

<style>
.empty-cart-wrapper {
    min-height: calc(100vh - 80px);
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.empty-cart-illustration {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 0 auto 3rem;
}

.cart-circle {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #f3f4f6 0%, #ffffff 100%);
    border-radius: 50%;
    box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.05),
        inset 0 -5px 15px rgba(0, 0, 0, 0.05);
    animation: pulse 2s ease-in-out infinite;
}

.cart-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 4rem;
    color: var(--primary-color);
    opacity: 0.8;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes bounce {
    0%, 100% { transform: translate(-50%, -50%); }
    50% { transform: translate(-50%, -60%); }
}

.empty-cart-info {
    text-align: center;
    max-width: 600px;
    margin: 0 auto 4rem;
}

.empty-cart-info h1 {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(45deg, var(--primary-color), var(--primary-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 1rem;
}

.empty-cart-info p {
    font-size: 1.2rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.primary-btn, .secondary-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.primary-btn {
    background: var(--primary-color);
    color: white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.primary-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    color: white;
}

.secondary-btn {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.secondary-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

.featured-section {
    max-width: 1200px;
    margin: 0 auto;
    padding-top: 4rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.section-header p {
    color: var(--text-muted);
    font-size: 1.1rem;
}

.featured-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    padding: 0 1rem;
}

.category-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.category-card i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.category-card span {
    display: block;
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .empty-cart-wrapper {
        padding: 3rem 1rem;
    }

    .empty-cart-info h1 {
        font-size: 2rem;
    }

    .empty-cart-info p {
        font-size: 1.1rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .primary-btn, .secondary-btn {
        width: 100%;
        justify-content: center;
    }

    .featured-categories {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .empty-cart-illustration {
        width: 150px;
        height: 150px;
    }

    .cart-icon {
        font-size: 3rem;
    }

    .empty-cart-info h1 {
        font-size: 1.75rem;
    }

    .section-header h2 {
        font-size: 1.5rem;
    }

    .featured-categories {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/../partials/footer.php';
?> 