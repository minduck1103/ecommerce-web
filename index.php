<?php
require_once 'config/database.php';
require_once 'config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Your One-Stop Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .feature-card {
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .cta-section {
            background-color: #f8f9fa;
            padding: 80px 0;
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
        .search-container {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 300px;
        }
        .search-container.active {
            display: block;
        }
        .search-input-group {
            display: flex;
        }
        .search-input-group input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .search-input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .admin-link:hover {
            background: rgba(0, 0, 0, 0.9);
            color: white;
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
                    <a href="#">Áo thun</a>
                    <a href="#">Áo sweater</a>
                    <a href="#">Áo khoác</a>
                    <a href="#">Quần dài</a>
                    <a href="#">Quần ngắn</a>
                </div>
            </div>
            <a class="navbar-brand mx-auto" href="index.php">ShopCart</a>
            <div class="d-flex align-items-center">
                <div class="position-relative">
                    <a href="#" class="text-white me-3" id="searchToggle">
                        <i class="fas fa-search fa-lg"></i>
                    </a>
                    <div class="search-container" id="searchContainer">
                        <form action="products.php" method="GET" class="search-input-group">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sản phẩm...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <a href="#" class="text-white me-3 dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">Tài khoản</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="login.php" class="text-white me-3">
                    <i class="fas fa-user fa-lg"></i>
                </a>
                <?php endif; ?>
                <a href="cart.php" class="text-white">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="badge bg-danger rounded-pill ms-1"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Welcome to ShopCart</h1>
            <p class="lead">Discover Amazing Products at Great Prices</p>
            <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-shipping-fast fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Fast Delivery</h5>
                            <p class="card-text">Get your orders delivered to your doorstep quickly and safely.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tags fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Best Prices</h5>
                            <p class="card-text">Enjoy competitive prices and regular discounts on our products.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-headset fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">24/7 Support</h5>
                            <p class="card-text">Our customer support team is always here to help you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Start Shopping?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers who trust us for their shopping needs.</p>
            <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
            <?php else: ?>
            <a href="products.php" class="btn btn-primary btn-lg">Browse Products</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Your trusted online shopping destination for quality products and excellent service.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <li><a href="#" class="text-white">Categories</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-envelope"></i> info@shopcart.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Shopping Street, City</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2024 ShopCart. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Admin Link -->
    <a href="admin/login.php" class="admin-link">
        <i class="fas fa-user-shield"></i> Admin Panel
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search toggle
            const searchToggle = document.getElementById('searchToggle');
            const searchContainer = document.getElementById('searchContainer');
            
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                searchContainer.classList.toggle('active');
                if (searchContainer.classList.contains('active')) {
                    searchContainer.querySelector('input').focus();
                }
            });
            
            // Close search when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchContainer.contains(e.target) && !searchToggle.contains(e.target)) {
                    searchContainer.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html> 