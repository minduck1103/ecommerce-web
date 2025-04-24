<?php
// Định nghĩa đường dẫn gốc
define('BASE_PATH', __DIR__);

// Lấy URL request
$request_uri = $_SERVER['REQUEST_URI'];
$base_url = '/shoppingcart';

// Loại bỏ base URL và query string
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace($base_url, '', $path);
$path = trim($path, '/');

// Routing
switch ($path) {
    case '':
    case 'home':
        require __DIR__ . '/app/views/home/index.php';
        break;
        
    case 'account/profile':
        require __DIR__ . '/app/views/account/profile.php';
        break;
        
    case 'account/orders':
        require __DIR__ . '/app/views/account/orders.php';
        break;
        
    case 'account/change-password':
        require __DIR__ . '/app/views/account/change-password.php';
        break;
        
    case 'auth/login':
        require __DIR__ . '/app/views/auth/login.php';
        break;
        
    case 'auth/register':
        require __DIR__ . '/app/views/auth/register.php';
        break;
        
    case 'products':
        require __DIR__ . '/app/views/products/index.php';
        break;
        
    case 'cart':
        require __DIR__ . '/app/views/cart/index.php';
        break;
        
    case 'cart/checkout':
        require __DIR__ . '/app/views/cart/checkout.php';
        break;
        
    default:
        // Kiểm tra xem có phải là product detail không
        if (preg_match('/^products\/detail\/(\d+)$/', $path, $matches)) {
            $_GET['id'] = $matches[1];
            require __DIR__ . '/app/views/products/detail.php';
            break;
        }
        
        // Kiểm tra xem có phải là API route không
        if (preg_match('/^api\/([^\/]+)\/([^\/]+)$/', $path, $matches)) {
            require __DIR__ . '/app/api/' . $matches[1] . '/' . $matches[2] . '.php';
            break;
        }
        
        // 404 Not Found
        http_response_code(404);
        require __DIR__ . '/app/views/errors/404.php';
        break;
} 