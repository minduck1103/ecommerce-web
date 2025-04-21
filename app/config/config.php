<?php

// Đường dẫn thư mục gốc của ứng dụng
define('APP_ROOT', dirname(dirname(__FILE__)));

// URL gốc của ứng dụng
define('BASE_URL', '/shoppingcart');

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shoppingcart');

// Cấu hình upload
define('UPLOAD_DIR', dirname(dirname(dirname(__FILE__))) . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Cấu hình session
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7); // 7 days
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7); // 7 days

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 