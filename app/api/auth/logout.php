<?php
session_start();

// Xóa tất cả các session variables
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Hủy session
session_destroy();

// Trả về response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Đăng xuất thành công'
]);
?> 