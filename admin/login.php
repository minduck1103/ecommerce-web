<?php
session_start();

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: /shoppingcart/admin/dashboard.php");
    exit;
}

// Define base URLs
define('BASE_URL', '/shoppingcart');
define('ADMIN_URL', BASE_URL . '/admin');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }

        body {
            background: linear-gradient(135deg, #f8f9fc 0%, #f1f3f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-header {
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .header-brand {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .header-brand:hover {
            color: var(--primary-color);
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
        }

        .input-group-text {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            background: #f8f9fc;
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #2e59d9;
            transform: translateY(-1px);
        }

        .back-link {
            color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        .admin-footer {
            background: white;
            padding: 1.5rem 0;
            margin-top: auto;
            box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.05);
        }

        .footer-text {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin: 0;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-danger {
            background-color: #fff5f5;
            color: #e53e3e;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?php echo ADMIN_URL; ?>" class="header-brand">
                    <i class="fas fa-shopping-bag me-2"></i>ShopCart Admin
                </a>
                <div>
                    <a href="<?php echo BASE_URL; ?>" class="back-link text-decoration-none">
                        <i class="fas fa-store me-1"></i>Xem cửa hàng
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Container -->
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="login-card">
                        <div class="card-body p-5">
                            <div class="login-header">
                                <h1 class="login-title">Đăng nhập</h1>
                                <p class="login-subtitle">Đăng nhập để quản lý cửa hàng của bạn</p>
                            </div>
                            
                            <?php if (isset($_SESSION['login_error'])): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php 
                                    echo $_SESSION['login_error'];
                                    unset($_SESSION['login_error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?php echo ADMIN_URL; ?>/auth/process_login.php" method="POST">
                                <div class="mb-4">
                                    <label for="username" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control" id="username" name="username" 
                                               placeholder="Nhập địa chỉ email" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Nhập mật khẩu" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-login btn-primary w-100 mb-4">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <p class="footer-text">
                    &copy; <?php echo date('Y'); ?> ShopCart Admin. All rights reserved.
                </p>
                <div class="footer-links">
                    <a href="#" class="back-link text-decoration-none me-3">
                        <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                    </a>
                    <a href="#" class="back-link text-decoration-none">
                        <i class="fas fa-file-alt me-1"></i>Terms of Service
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 