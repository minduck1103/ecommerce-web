<?php

class HomeController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $productModel = $this->model('Product');
        $bestSellers = $productModel->getBestSellers(8);
        $newArrivals = $productModel->getLatestProducts(8);
        
        $this->render('home/index', [
            'title' => 'Trang chủ',
            'bestSellers' => $bestSellers,
            'newArrivals' => $newArrivals
        ]);
    }
} 