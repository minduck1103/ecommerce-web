<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle delete product
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    header('Location: products.php?message=Product deleted successfully');
    exit;
}

// Get all products with their categories
$stmt = $conn->query("SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - ShopCart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .sidebar a.active {
            background: #0d6efd;
        }
        .main-content {
            padding: 20px;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4>Admin Panel</h4>
                </div>
                <a href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="products.php" class="active">
                    <i class="fas fa-box me-2"></i> Quản lý sản phẩm
                </a>
                <a href="categories.php">
                    <i class="fas fa-tags me-2"></i> Quản lý danh mục
                </a>
                <a href="users.php">
                    <i class="fas fa-users me-2"></i> Quản lý người dùng
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Quản lý sản phẩm</h2>
                    <a href="product-form.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
                    </a>
                </div>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="product-image">
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                                            <td><?php echo $product['quantity']; ?></td>
                                            <td>
                                                <?php if ($product['quantity'] > 0): ?>
                                                    <span class="badge bg-success">Còn hàng</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Hết hàng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="product-form.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 