<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 80px;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: white !important;
            border-bottom: 1px solid #eee;
            height: 80px;
            padding: 0;
        }

        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            padding: 0;
        }

        .navbar-brand img {
            height: 40px;
        }

        .menu-btn {
            background: none;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 1.25rem;
            color: #000;
            cursor: pointer;
        }

        .right-icons {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .icon-btn {
            background: none;
            border: none;
            padding: 0;
            font-size: 1.25rem;
            color: #000;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .search-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: white;
            display: none;
            align-items: center;
            padding: 0 1rem;
            z-index: 1001;
        }

        .search-container.active {
            display: flex;
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 0.5rem;
            font-size: 1rem;
            outline: none;
        }

        .close-search {
            background: none;
            border: none;
            padding: 0.5rem;
            font-size: 1.25rem;
            color: #000;
            cursor: pointer;
        }

        .offcanvas {
            width: 300px;
        }

        .offcanvas-title {
            font-weight: 600;
        }

        .nav-menu-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-menu-links li a {
            display: block;
            padding: 0.75rem 1rem;
            color: #000;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-menu-links li a:hover {
            background-color: #f8f9fa;
        }

        .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item i {
            width: 20px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="container">
            <!-- Menu Button -->
            <button class="menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuOffcanvas">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="/shoppingcart">
                <img src="/shoppingcart/public/images/logo.png" alt="Shopping Cart">
            </a>

            <!-- Right Icons -->
            <div class="right-icons">
                <button class="icon-btn" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <button class="icon-btn" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                        <li>
                            <a class="dropdown-item" href="/shoppingcart/account">
                                <i class="fas fa-user-circle"></i>
                                Thông tin tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/shoppingcart/account/orders">
                                <i class="fas fa-shopping-bag"></i>
                                Đơn hàng của tôi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/shoppingcart/auth/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="/shoppingcart/auth/login" class="icon-btn">
                    <i class="fas fa-user"></i>
                </a>
                <?php endif; ?>
                <a href="/shoppingcart/cart" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
                </a>
            </div>

            <!-- Search Container -->
            <div class="search-container" id="searchContainer">
                <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                <button class="close-search" id="closeSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Off-canvas Menu -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="menuOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav-menu-links">
                <li><a href="/shoppingcart">Trang chủ</a></li>
                <li><a href="/shoppingcart/products">Sản phẩm</a></li>
                <li><a href="/shoppingcart/about">Giới thiệu</a></li>
                <li><a href="/shoppingcart/contact">Liên hệ</a></li>
            </ul>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const closeSearch = document.getElementById('closeSearch');
    const searchContainer = document.getElementById('searchContainer');
    const searchInput = document.querySelector('.search-input');

    searchBtn.addEventListener('click', function() {
        searchContainer.classList.add('active');
        searchInput.focus();
    });

    closeSearch.addEventListener('click', function() {
        searchContainer.classList.remove('active');
    });

    // Close search on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchContainer.classList.contains('active')) {
            searchContainer.classList.remove('active');
        }
    });

    // Search form submission
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                window.location.href = `/shoppingcart/products/search?q=${encodeURIComponent(searchTerm)}`;
            }
        }
    });
});

// Cart count update functionality
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        if (count > 0) {
            cartCount.textContent = count;
            cartCount.style.display = 'flex';
        } else {
            cartCount.style.display = 'none';
        }
    }
}

// Function to fetch current cart count from server
function fetchCartCount() {
    fetch('/shoppingcart/cart/count')
        .then(response => response.json())
        .then(data => {
            updateCartCount(data.count);
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

// Update cart count every 5 seconds
setInterval(fetchCartCount, 5000);

// Custom event for instant cart updates
document.addEventListener('cartUpdated', function(e) {
    updateCartCount(e.detail.count);
});

// Function to trigger cart count update
function triggerCartUpdate(count) {
    document.dispatchEvent(new CustomEvent('cartUpdated', {
        detail: { count: count }
    }));
}
</script>
</body>
</html> 