<?php
// Include config file
require_once __DIR__ . '/app/config/config.php';

// Định nghĩa đường dẫn gốc
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload các class
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Lấy URL từ query string
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Xác định controller và action
$controller = !empty($url[0]) ? $url[0] : 'home';
$action = !empty($url[1]) ? $url[1] : 'index';
$params = array_slice($url, 2);

// Format tên controller
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

// Try singular version if plural file doesn't exist
if (!file_exists($controllerFile) && substr($controller, -1) === 's') {
    $singularController = rtrim($controller, 's');
    $controllerName = ucfirst($singularController) . 'Controller';
    $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
}

// Kiểm tra và gọi controller
if (!file_exists($controllerFile)) {
    require_once APP_PATH . '/views/404.php';
    exit();
}

require_once $controllerFile;
$controllerInstance = new $controllerName();

if (!method_exists($controllerInstance, $action)) {
    require_once APP_PATH . '/views/404.php';
    exit();
}

call_user_func_array([$controllerInstance, $action], $params); 