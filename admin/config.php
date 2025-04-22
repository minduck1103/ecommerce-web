<?php
// Định nghĩa đường dẫn cơ sở cho admin
define('BASE_URL', '/shoppingcart');
define('ADMIN_URL', BASE_URL . '/admin');

// Hàm helper để tạo URL đầy đủ
function url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

// Hàm helper để tạo URL admin
function admin_url($path = '') {
    return ADMIN_URL . ($path ? '/' . ltrim($path, '/') : '');
}

// Hàm chuyển hướng
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

// Kiểm tra đăng nhập admin
function check_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect(admin_url('/login.php'));
    }
}

// Kiểm tra và ngăn chặn truy cập nếu đã đăng nhập
function check_admin_logged() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        redirect(admin_url('/dashboard.php'));
    }
} 