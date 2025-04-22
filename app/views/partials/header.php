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
            display: flex;
            align-items: center;
        }

        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .navbar-brand img {
            height: 60px;
            width: auto;
            object-fit: contain;
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
            font-size: 1.25rem;
            font-weight: 600;
        }

        .nav-menu-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-menu-links li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-menu-links li a:hover {
            background-color: #f8f9fa;
            color: #4CAF50;
        }

        .nav-menu-links li a i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-divider {
            height: 1px;
            background-color: #eee;
            margin: 0.5rem 0;
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
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-4">
            <button class="menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand" href="/shoppingcart">
                <img src="/shoppingcart/public/images/logo.jpg" alt="Logo">
            </a>

            <div class="right-icons">
                <button class="icon-btn" onclick="toggleSearch()">
                    <i class="fas fa-search"></i>
                </button>
                <a href="/shoppingcart/cart" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="icon-btn" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/shoppingcart/profile"><i class="fas fa-user-circle"></i> Tài khoản của tôi</a></li>
                            <li><a class="dropdown-item" href="/shoppingcart/orders"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/shoppingcart/logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/shoppingcart/login" class="icon-btn">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="search-container">
                <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                <button class="close-search" onclick="toggleSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start" id="navMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav-menu-links">
                <li><a href="/shoppingcart"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="/shoppingcart/products"><i class="fas fa-tshirt"></i> Sản phẩm</a></li>
                <li><a href="/shoppingcart/about"><i class="fas fa-info-circle"></i> Về chúng tôi</a></li>
                <li><a href="/shoppingcart/contact"><i class="fas fa-envelope"></i> Liên hệ</a></li>
            </ul>
        </div>
    </div>
</header>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Cart JavaScript -->
<script src="/shoppingcart/public/js/cart.js"></script>

<script>
    function toggleSearch() {
        document.querySelector('.search-container').classList.toggle('active');
        if (document.querySelector('.search-container').classList.contains('active')) {
            document.querySelector('.search-input').focus();
        }
    }
</script>
</body>
</html> 