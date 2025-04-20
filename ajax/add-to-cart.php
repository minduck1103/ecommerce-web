<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get product ID and quantity
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Validate quantity
if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
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

// Check stock
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock']);
    exit;
}

// Add to cart
addToCart($product_id, $quantity);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cartCount' => count($_SESSION['cart'])
]);
?> 