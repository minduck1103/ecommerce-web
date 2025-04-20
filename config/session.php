<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Function to require admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to add item to cart
function addToCart($product_id, $quantity = 1) {
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Function to update cart item quantity
function updateCartQuantity($product_id, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($product_id);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Function to remove item from cart
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Function to get cart total
function getCartTotal($conn) {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($_SESSION['cart']));
        while ($product = $stmt->fetch()) {
            $total += $product['price'] * $_SESSION['cart'][$product['id']];
        }
    }
    return $total;
}

// Function to get cart items
function getCartItems($conn) {
    $items = [];
    if (!empty($_SESSION['cart'])) {
        $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
        $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($_SESSION['cart']));
        while ($product = $stmt->fetch()) {
            $items[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $_SESSION['cart'][$product['id']]
            ];
        }
    }
    return $items;
}
?> 