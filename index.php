<?php
session_start();
require_once 'app/config/database.php';

// Lấy URL path
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/shoppingcart';

// Loại bỏ base path từ URL
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Định nghĩa routes
$routes = [
    '/' => 'app/views/home/index.php',
    '/products' => 'app/views/products/index.php',
    '/cart' => 'app/views/cart/index.php',
    '/account/orders' => 'app/views/account/orders.php',
    '/login' => 'app/views/auth/login.php',
    '/register' => 'app/views/auth/register.php',
    '/logout' => 'app/views/auth/logout.php'
];

// Kiểm tra route tồn tại
if (isset($routes[$request_uri])) {
    $file_path = __DIR__ . '/' . $routes[$request_uri];
    if (file_exists($file_path)) {
        require $file_path;
        exit;
    }
}

// Kiểm tra API routes
if (strpos($request_uri, '/api/') === 0) {
    $api_file = __DIR__ . '/app' . $request_uri . '.php';
    if (file_exists($api_file)) {
        require $api_file;
        exit;
    }
}

// Nếu không tìm thấy route, hiển thị trang 404
http_response_code(404);
require 'app/views/error/404.php'; 