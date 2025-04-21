<?php

class CartModel extends BaseModel {
    protected $table = 'cart';

    public function getCartItems($userId) {
        $productModel = new ProductModel();
        $cartItems = [];
        
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $product = $productModel->getProductById($productId);
                if ($product) {
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'price' => $product['price'],
                        'subtotal' => $product['price'] * $quantity
                    ];
                }
            }
        }
        
        return $cartItems;
    }

    public function addToCart($productId, $quantity = 1) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    public function updateCart($productId, $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    public function removeFromCart($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }

    public function clearCart($userId) {
        $_SESSION['cart'] = [];
    }

    public function getCartTotal() {
        $total = 0;
        $cartItems = $this->getCartItems($_SESSION['user_id'] ?? 0);
        
        foreach ($cartItems as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }

    public function getCartCount() {
        if (!isset($_SESSION['cart'])) {
            return 0;
        }
        return array_sum($_SESSION['cart']);
    }
} 