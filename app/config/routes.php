<?php
// Định nghĩa các routes
return [
    // Trang chủ
    '/' => [
        'path' => 'views/home/index.php',
        'auth' => false
    ],
    
    // Sản phẩm
    '/products' => [
        'path' => 'views/products/index.php',
        'auth' => false
    ],
    
    // Giỏ hàng
    '/cart' => [
        'path' => 'views/cart/index.php',
        'auth' => true
    ],
    
    // Đơn hàng
    '/account/orders' => [
        'path' => 'views/account/orders.php',
        'auth' => true
    ],
    
    // Đăng nhập/Đăng ký
    '/login' => [
        'path' => 'views/auth/login.php',
        'auth' => false
    ],
    '/register' => [
        'path' => 'views/auth/register.php',
        'auth' => false
    ],
    '/logout' => [
        'path' => 'views/auth/logout.php',
        'auth' => true
    ],
    
    // Admin routes
    '/admin' => [
        'path' => 'views/admin/index.php',
        'auth' => true,
        'admin' => true
    ],
    '/admin/products' => [
        'path' => 'views/admin/products.php',
        'auth' => true,
        'admin' => true
    ],
    '/admin/categories' => [
        'path' => 'views/admin/categories.php',
        'auth' => true,
        'admin' => true
    ],
    '/admin/orders' => [
        'path' => 'views/admin/orders.php',
        'auth' => true,
        'admin' => true
    ],
    '/admin/users' => [
        'path' => 'views/admin/users.php',
        'auth' => true,
        'admin' => true
    ]
]; 