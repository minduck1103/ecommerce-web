<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-dropdown {
            position: relative;
            display: inline-block;
        }
        .category-menu {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        .category-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .category-menu a:hover {
            background-color: #f1f1f1;
        }
        .category-dropdown:hover .category-menu {
            display: block;
        }
        .cart-item {
            transition: background-color 0.3s;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        .quantity-input {
            width: 70px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div class="category-dropdown">
                <a href="#" class="text-white me-3">
                    <i class="fas fa-bars fa-lg"></i>
                </a>
                <div class="category-menu">
                    <a href="products.php">Tất cả sản phẩm</a>
                    <a href="#">Áo thun</a>
                    <a href="#">Áo sweater</a>
                    <a href="#">Áo khoác</a>
                    <a href="#">Quần dài</a>
                    <a href="#">Quần ngắn</a>
                </div>
            </div>
            <a class="navbar-brand mx-auto" href="index.php">ShopCart</a>
            <div class="d-flex align-items-center">
                <a href="#" class="text-white me-3">
                    <i class="fas fa-search fa-lg"></i>
                </a>
                <a href="signin.php" class="text-white me-3">
                    <i class="fas fa-user fa-lg"></i>
                </a>
                <a href="cart.php" class="text-white">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="badge bg-danger rounded-pill ms-1">3</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4">Giỏ hàng của bạn</h2>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items -->
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Cart Item 1 -->
                        <div class="row mb-3 cart-item p-3 border-bottom">
                            <div class="col-md-2">
                                <img src="https://via.placeholder.com/100x100" class="img-fluid rounded" alt="Product">
                            </div>
                            <div class="col-md-4">
                                <h5 class="card-title">Áo thun basic</h5>
                                <p class="text-muted">Màu: Đen | Size: M</p>
                            </div>
                            <div class="col-md-2">
                                <p class="text-danger fw-bold">199.000đ</p>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group quantity-input">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                    <input type="text" class="form-control text-center" value="1">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Cart Item 2 -->
                        <div class="row mb-3 cart-item p-3 border-bottom">
                            <div class="col-md-2">
                                <img src="https://via.placeholder.com/100x100" class="img-fluid rounded" alt="Product">
                            </div>
                            <div class="col-md-4">
                                <h5 class="card-title">Áo sweater</h5>
                                <p class="text-muted">Màu: Xám | Size: L</p>
                            </div>
                            <div class="col-md-2">
                                <p class="text-danger fw-bold">299.000đ</p>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group quantity-input">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                    <input type="text" class="form-control text-center" value="1">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Cart Item 3 -->
                        <div class="row mb-3 cart-item p-3">
                            <div class="col-md-2">
                                <img src="https://via.placeholder.com/100x100" class="img-fluid rounded" alt="Product">
                            </div>
                            <div class="col-md-4">
                                <h5 class="card-title">Quần jean</h5>
                                <p class="text-muted">Màu: Xanh | Size: 32</p>
                            </div>
                            <div class="col-md-2">
                                <p class="text-danger fw-bold">259.000đ</p>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group quantity-input">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                    <input type="text" class="form-control text-center" value="1">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="d-flex justify-content-between">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                    </a>
                    <button class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i>Xóa giỏ hàng
                    </button>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tổng đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính</span>
                            <span>757.000đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển</span>
                            <span>30.000đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Giảm giá</span>
                            <span class="text-danger">-50.000đ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Tổng cộng</span>
                            <span class="fw-bold text-danger">737.000đ</span>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div class="mb-3">
                            <label for="coupon" class="form-label">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon" placeholder="Nhập mã">
                                <button class="btn btn-outline-primary" type="button">Áp dụng</button>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary w-100">Thanh toán</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 