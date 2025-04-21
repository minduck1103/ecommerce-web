<?php
require_once 'BaseController.php';

class OrderController extends BaseController {
    private $orderModel;
    private $cartModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = $this->model('OrderModel');
        $this->cartModel = $this->model('CartModel');
    }

    public function index() {
        $this->checkout();
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /shoppingcart/auth/login');
            exit;
        }

        $cartItems = $this->cartModel->getCartItems($_SESSION['user_id']);
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = 30000; // Phí vận chuyển cố định
        $total = $subtotal + $shipping;

        $this->render('cart/checkout', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }

    public function create() {
        if (!$this->isLoggedIn()) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đặt hàng'
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        // Lấy thông tin giỏ hàng
        $cartItems = $this->cartModel->getCartItems($_SESSION['user_id']);
        if (empty($cartItems)) {
            $this->json([
                'success' => false,
                'message' => 'Giỏ hàng trống'
            ]);
            return;
        }

        // Tính tổng tiền
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // Chuẩn bị dữ liệu đơn hàng
        $orderData = [
            'user_id' => $_SESSION['user_id'],
            'full_name' => $data['firstName'] . ' ' . $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'note' => $data['note'] ?? '',
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ];

        try {
            $this->orderModel->beginTransaction();

            // Tạo đơn hàng
            $orderId = $this->orderModel->createOrder($orderData);

            // Thêm chi tiết đơn hàng
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product']['id'],
                    'product_name' => $item['product']['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
                $this->orderModel->addOrderItem($orderItemData);
            }

            // Xóa giỏ hàng
            $this->cartModel->clearCart($_SESSION['user_id']);

            $this->orderModel->commit();

            $this->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'orderId' => $orderId
            ]);
        } catch (Exception $e) {
            $this->orderModel->rollback();
            $this->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.'
            ]);
        }
    }

    public function confirmation($orderId) {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
            return;
        }

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
            $this->redirect('/');
            return;
        }

        $orderItems = $this->orderModel->getOrderItems($orderId);
        
        $this->render('order/confirmation', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 