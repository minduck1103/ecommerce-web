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

// Get product ID and quantity
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Check if product exists and is in stock
$stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status != 'out-of-stock'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found or out of stock']);
    exit;
}

// If quantity is 0, remove from cart
if ($quantity === 0) {
    removeFromCart($product_id);
} else {
    // Check stock
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }
    
    // Update quantity
    updateCartQuantity($product_id, $quantity);
}

// Get updated cart total
$cart_total = getCartTotal($conn);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Cart updated',
    'cartCount' => count($_SESSION['cart']),
    'subtotal' => $cart_total
]);
?> 