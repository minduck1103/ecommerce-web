<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Clear cart
$_SESSION['cart'] = [];

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Cart cleared',
    'cartCount' => 0,
    'subtotal' => 0
]);
?> 