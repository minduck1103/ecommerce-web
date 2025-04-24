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
            text-decoration: none;
        }

        .icon-btn:hover {
            color: #4a4a4a;
        }

        .icon-btn i {
            color: inherit;
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
            position: relative;
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }

        .search-input {
            width: 0;
            border: none;
            padding: 0.5rem;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
            border-radius: 20px;
            background-color: #f5f5f5;
            opacity: 0;
        }

        .search-input.active {
            width: 200px;
            opacity: 1;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
        }

        .search-input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }

        .close-search {
            display: none;
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
            border: none;
            border-radius: 0.5rem;
        }

        .dropdown-menu.show {
            display: block;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #4CAF50;
        }

        .dropdown-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #eee;
        }

        .user-info {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .user-info .user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .user-info .user-email {
            font-size: 0.9rem;
            color: #666;
        }

        .logout-item {
            color: #dc3545 !important;
        }

        .logout-item:hover {
            background-color: #fff5f5 !important;
            color: #dc3545 !important;
        }

        @media (max-width: 768px) {
            .navbar-brand img {
                height: 50px;
            }

            .right-icons {
                gap: 1rem;
            }

            .icon-btn {
                font-size: 1.1rem;
            }

            .search-input.active {
                width: 150px;
            }
        }

        .modal-content {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .modal-header {
            padding: 1.5rem 1.5rem 1rem;
        }

        .modal-body {
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            color: #333;
        }

        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #e9ecef;
            border-color: #ddd;
        }

        .btn-primary {
            background-color: #333;
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #222;
            transform: translateY(-1px);
        }

        .modal .btn-close {
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        .modal .btn-close:hover {
            opacity: 1;
        }

        /* Thêm styles cho modal yêu cầu đăng nhập */
        #loginRequiredModal .modal-content {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        #loginRequiredModal .modal-title {
            font-size: 1.25rem;
            display: flex;
            align-items: center;
        }

        #loginRequiredModal .modal-title i {
            font-size: 1.5rem;
            color: #ffc107;
        }

        #loginRequiredModal .modal-body {
            font-size: 1.1rem;
            color: #555;
        }

        #loginRequiredModal .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #loginRequiredModal .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        #loginRequiredModal .btn-secondary:hover {
            background-color: #e9ecef;
        }

        #loginRequiredModal .btn-primary {
            background-color: #333;
            border: none;
        }

        #loginRequiredModal .btn-primary:hover {
            background-color: #222;
            transform: translateY(-1px);
        }

        #loginRequiredModal .modal-footer {
            gap: 1rem;
        }
    </style>
    <script src="/shoppingcart/public/js/toast.js"></script>
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
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Tìm kiếm...">
                    <button class="icon-btn" onclick="toggleSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <a href="/shoppingcart/cart" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="icon-btn" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                            <li class="user-info">
                                <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                                <div class="user-email"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
                            </li>
                            <li><a class="dropdown-item" href="/shoppingcart/account/profile">
                                <i class="fas fa-user-circle"></i>Tài khoản của tôi
                            </a></li>
                            <li><a class="dropdown-item" href="/shoppingcart/account/orders">
                                <i class="fas fa-shopping-bag"></i>Đơn hàng của tôi
                            </a></li>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="/shoppingcart/admin">
                                    <i class="fas fa-cog"></i>Quản trị
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-item" href="#" onclick="logout(event)">
                                <i class="fas fa-sign-out-alt"></i>Đăng xuất
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/shoppingcart/auth/login" class="icon-btn">
                        <i class="fas fa-user" style="color: #000;"></i>
                    </a>
                <?php endif; ?>
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

<!-- Thêm modal xác nhận đăng xuất -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="logoutModalLabel">Xác nhận đăng xuất</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn đăng xuất?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmLogout">Đăng xuất</button>
            </div>
        </div>
    </div>
</div>

<!-- Thêm modal yêu cầu đăng nhập -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginRequiredModalLabel">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    Yêu cầu đăng nhập
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-0">Vui lòng đăng nhập để xem giỏ hàng của bạn!</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Để sau</button>
                <a href="/shoppingcart/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Đăng nhập ngay
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSearch() {
        const searchInput = document.querySelector('.search-input');
        searchInput.classList.toggle('active');
        if (searchInput.classList.contains('active')) {
            searchInput.focus();
        }
    }

    // Thêm xử lý sự kiện khi click ra ngoài ô tìm kiếm
    document.addEventListener('click', function(event) {
        const searchContainer = document.querySelector('.search-container');
        const searchInput = document.querySelector('.search-input');
        
        if (!searchContainer.contains(event.target) && searchInput.classList.contains('active')) {
            searchInput.classList.remove('active');
        }
    });

    // Xử lý sự kiện khi nhấn Enter trong ô tìm kiếm
    document.querySelector('.search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                window.location.href = `/shoppingcart/products?search=${encodeURIComponent(searchTerm)}`;
            }
        }
    });
    
    // Khởi tạo tất cả các dropdown
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo dropdown bằng Bootstrap API
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        // Thêm xử lý sự kiện click cho icon account
        var accountDropdown = document.getElementById('accountDropdown');
        if (accountDropdown) {
            accountDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                var dropdownMenu = this.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    dropdownMenu.classList.toggle('show');
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Lấy các phần tử cần thiết
        const logoutBtn = document.querySelector('.logout-btn');
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
        const confirmLogoutBtn = document.getElementById('confirmLogout');

        // Xử lý sự kiện click vào nút đăng xuất
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.show();
            });
        }

        // Xử lý sự kiện xác nhận đăng xuất
        if (confirmLogoutBtn) {
            confirmLogoutBtn.addEventListener('click', async function() {
                try {
                    const response = await fetch('/shoppingcart/app/api/auth/logout.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        // Ẩn modal
                        logoutModal.hide();
                        // Chuyển hướng về trang chủ
                        window.location.href = '/shoppingcart';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    alert('Có lỗi xảy ra khi đăng xuất. Vui lòng thử lại.');
                }
            });
        }

        // Xử lý click vào icon giỏ hàng
        const cartIcon = document.querySelector('a[href="/shoppingcart/cart"]');
        if (cartIcon) {
            cartIcon.addEventListener('click', function(e) {
                <?php if (!isset($_SESSION['user_id'])): ?>
                e.preventDefault();
                const loginRequiredModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
                loginRequiredModal.show();
                <?php endif; ?>
            });
        }
    });

    // Xử lý đăng xuất
    function logout(event) {
        event.preventDefault();
        
        fetch('/shoppingcart/app/api/auth/logout.php', {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/shoppingcart';
            } else {
                alert('Có lỗi xảy ra khi đăng xuất');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi đăng xuất');
        });
    }
</script>
</body>
</html> 