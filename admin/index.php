<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni Clothing Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-white">
        <div class="container">
            <a class="navbar-brand text-dark text-bold" href="#">Uni Clothing</a>
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Chào mừng đến với Uni Clothing Admin</h1>
            <p class="lead">Công cụ quản lý mạnh mẽ cho nền tảng thương mại điện tử của bạn</p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-box feature-icon"></i>
                            <h3>Quản lý Sản phẩm</h3>
                            <p>Dễ dàng quản lý danh mục sản phẩm, cập nhật giá và kiểm soát tồn kho.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shopping-cart feature-icon"></i>
                            <h3>Xử lý Đơn hàng</h3>
                            <p>Theo dõi và quản lý đơn hàng, cập nhật trạng thái và xử lý yêu cầu khách hàng.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users feature-icon"></i>
                            <h3>Quản lý Người dùng</h3>
                            <p>Quản lý tài khoản người dùng, phân quyền và bảo mật thông tin.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Sẵn sàng để bắt đầu?</h2>
            <a href="login.php" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 ShopCart. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 