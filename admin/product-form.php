<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'quantity' => '',
    'category_id' => '',
    'image' => ''
];

// Get categories for dropdown
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// If editing existing product
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $image = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image = 'uploads/products/' . $file_name;
            
            // Delete old image if exists
            if ($product['image'] && file_exists('../' . $product['image'])) {
                unlink('../' . $product['image']);
            }
        }
    }
    
    if (isset($_GET['id'])) {
        // Update existing product
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                               quantity = ?, category_id = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $quantity, $category_id, $image, $_GET['id']]);
        $message = "Product updated successfully";
    } else {
        // Add new product
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, quantity, 
                               category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $quantity, $category_id, $image]);
        $message = "Product added successfully";
    }
    
    header('Location: products.php?message=' . urlencode($message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Product - ShopCart Admin</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
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
                    <h2><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Product</h2>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Products
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price</label>
                                                <input type="number" class="form-control" id="price" name="price" 
                                                       value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                                       value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="image" name="image" 
                                               accept="image/*" <?php echo !isset($_GET['id']) ? 'required' : ''; ?>>
                                        <?php if ($product['image']): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="Current product image" class="preview-image">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo isset($_GET['id']) ? 'Update' : 'Add'; ?> Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 