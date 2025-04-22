<?php
session_start();
require_once 'config/error_handler.php';

// Lấy request URI và loại bỏ query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Loại bỏ base path từ request
$base_path = '/shoppingcart';
$request_uri = str_replace($base_path, '', $request_uri);

// Load routes
$routes = require_once 'config/routes.php';

// Kiểm tra route tồn tại
if (isset($routes[$request_uri])) {
    $route = $routes[$request_uri];
    
    // Kiểm tra xác thực nếu cần
    if ($route['auth'] && !isset($_SESSION['user_id'])) {
        header('Location: /shoppingcart/login');
        exit;
    }
    
    // Kiểm tra quyền admin nếu cần
    if (isset($route['admin']) && $route['admin']) {
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            header('Location: /shoppingcart');
            exit;
        }
    }
    
    $file_path = __DIR__ . '/' . $route['path'];
    if (file_exists($file_path)) {
        require $file_path;
    } else {
        // Log lỗi để debug
        error_log("File not found: " . $file_path);
        handle404();
    }
} else {
    // Kiểm tra nếu là request API
    if (strpos($request_uri, '/api/') === 0) {
        $api_file = __DIR__ . $request_uri . '.php';
        if (file_exists($api_file)) {
            require $api_file;
        } else {
            // Log lỗi để debug
            error_log("API file not found: " . $api_file);
            handle404();
        }
    } else {
        // Log lỗi để debug
        error_log("Route not found: " . $request_uri);
        handle404();
    }
} 