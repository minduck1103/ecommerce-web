<?php

class HomeController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $productModel = $this->model('Product');
        
        // Lấy danh sách sản phẩm mới nhất và best seller
        $newArrivals = $productModel->getLatestProducts(8);
        $bestSellers = $productModel->getBestSellers(8);
        
        // Render view với dữ liệu
        $this->render('home/index', [
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'title' => 'Trang chủ'
        ]);
    }
} 