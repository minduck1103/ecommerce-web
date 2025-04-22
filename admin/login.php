<?php
session_start();
require_once 'config.php';

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo admin_url('/assets/css/admin.css'); ?>" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo admin_url(); ?>">ShopCart Admin</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 56px);">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Đăng nhập</h2>
                            <p class="text-muted">Đăng nhập để quản lý cửa hàng</p>
                        </div>
                        
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['login_error'];
                                unset($_SESSION['login_error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="auth/process_login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="<?php echo admin_url(); ?>" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 