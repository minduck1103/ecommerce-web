<?php
require_once 'BaseController.php';

class CartController extends BaseController {
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function index() {
        $productModel = $this->model('Product');
        $cartItems = [];
        $total = 0;
        
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $productModel->getProductById($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product['price'] * $quantity
                ];
                $total += $product['price'] * $quantity;
            }
        }
        
        $this->render('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'title' => 'Giỏ hàng'
        ]);
    }
    
    public function add() {
        if (!isset($_SESSION['user_id'])) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng'
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? 0;
        $quantity = $data['quantity'] ?? 1;
        
        if (!$productId) {
            $this->json([
                'success' => false,
                'message' => 'Sản phẩm không hợp lệ'
            ]);
            return;
        }
        
        // Kiểm tra sản phẩm tồn tại
        $productModel = $this->model('Product');
        $product = $productModel->getProductById($productId);
        
        if (!$product) {
            $this->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ]);
            return;
        }
        
        // Thêm vào giỏ hàng
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        $cartCount = array_sum($_SESSION['cart']);
        
        $this->json([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng',
            'cartCount' => $cartCount
        ]);
    }
    
    public function update() {
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? 0;
        $quantity = $data['quantity'] ?? 0;
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        $productModel = $this->model('Product');
        $product = $productModel->getProductById($productId);
        $subtotal = $product ? $product['price'] * $quantity : 0;
        $total = 0;
        
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $p = $productModel->getProductById($pid);
            if ($p) {
                $total += $p['price'] * $qty;
            }
        }
        
        $cart_count = array_sum($_SESSION['cart']);
        
        $this->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total' => $total,
            'cart_count' => $cart_count,
            'message' => $quantity <= 0 ? 'Đã xóa sản phẩm khỏi giỏ hàng' : 'Đã cập nhật giỏ hàng'
        ]);
    }
    
    public function remove() {
        $productId = $_POST['product_id'] ?? 0;
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        
        $productModel = $this->model('Product');
        $total = 0;
        
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $product = $productModel->getProductById($pid);
            if ($product) {
                $total += $product['price'] * $qty;
            }
        }
        
        $cartCount = array_sum($_SESSION['cart']);
        
        $this->json([
            'success' => true,
            'total' => $total,
            'cartCount' => $cartCount
        ]);
    }
    
    public function getCount() {
        $count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
        $this->json(['count' => $count]);
    }
} 