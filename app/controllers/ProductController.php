<?php
require_once 'BaseController.php';

class ProductController extends BaseController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        $categories = $this->categoryModel->getAllCategories();
        
        $this->render('products/index', [
            'title' => 'Tất cả sản phẩm',
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            $this->redirect('/products');
        }

        $this->render('products/show', [
            'title' => $product['name'],
            'product' => $product
        ]);
    }

    public function detail($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            $this->redirect('products');
            return;
        }
        
        $this->render('products/detail', [
            'title' => $product['name'],
            'product' => $product
        ]);
    }

    public function filter() {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->redirect('products');
            return;
        }
        
        $category = $_GET['category'] ?? null;
        $priceFrom = isset($_GET['price_from']) ? (float)$_GET['price_from'] : null;
        $priceTo = isset($_GET['price_to']) ? (float)$_GET['price_to'] : null;
        $sort = $_GET['sort'] ?? 'newest';
        
        $products = $this->productModel->filterProducts($category, $priceFrom, $priceTo, $sort);
        
        $this->json([
            'success' => true,
            'html' => $this->renderPartial('products/_product_grid', ['products' => $products])
        ]);
    }

    public function addToCart() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng']);
            exit;
        }
        
        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        $size = $_POST['size'] ?? null;
        
        if (!$productId || !$size) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn size sản phẩm']);
            exit;
        }
        
        $cartModel = $this->model('Cart');
        $result = $cartModel->addItem($_SESSION['user_id'], $productId, $quantity, $size);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Đã thêm sản phẩm vào giỏ hàng' : 'Có lỗi xảy ra'
        ]);
    }
} 