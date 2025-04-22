<?php
// Thiết lập các tham số session trước khi start
if (session_status() === PHP_SESSION_NONE) {
    // Thiết lập thời gian sống của session (2 giờ)
    ini_set('session.gc_maxlifetime', 7200);
    session_set_cookie_params(7200);

    // Thiết lập session secure nếu sử dụng HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    // Thiết lập httponly cho cookie session
    ini_set('session.cookie_httponly', 1);

    // Thiết lập SameSite
    ini_set('session.cookie_samesite', 'Lax');

    // Start session
    session_start();
}

// Regenerate session ID định kỳ để tránh session fixation
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} 