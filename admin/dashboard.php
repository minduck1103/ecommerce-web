<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include required files
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once 'auth.php';

// Check admin authentication first
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Debug information
echo "<!-- Debug Info -->";
echo "<div style='display:none'>";
echo "Session: ";
print_r($_SESSION);
echo "<br>Current Directory: " . __DIR__;
echo "<br>Included Files: ";
print_r(get_included_files());
echo "</div>";

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = 'Email hoặc mật khẩu không đúng';
            header("Location: login.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['login_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        header("Location: login.php");
        exit;
    }
}

// Get statistics
try {
    $stats = [
        'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn()
    ];
} catch (PDOException $e) {
    echo "<!-- Database Error: " . $e->getMessage() . " -->";
    $stats = [
        'products' => 0,
        'categories' => 0,
        'users' => 0,
        'orders' => 0
    ];
}

// Get categories for product form
try {
    $categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Lưu thông tin session vào biến JavaScript
$sessionData = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'username' => $_SESSION['username'] ?? null
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
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
            cursor: pointer;
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .section {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .section.active {
            display: block;
            opacity: 1;
        }
        #loadingSpinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            display: none;
            background: rgba(255,255,255,0.8);
            padding: 2rem;
            border-radius: 10px;
        }
        .section-loading {
            position: relative;
            min-height: 200px;
        }
        .section-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
        }
        .section-loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 1001;
        }
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="position-fixed top-50 start-50 translate-middle d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4>Admin Panel</h4>
                </div>
                <a href="#" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="#" class="nav-link" data-section="products">
                    <i class="fas fa-box me-2"></i> Quản lý sản phẩm
                </a>
                <a href="#" class="nav-link" data-section="categories">
                    <i class="fas fa-tags me-2"></i> Quản lý danh mục
                </a>
                <a href="#" class="nav-link" data-section="users">
                    <i class="fas fa-users me-2"></i> Quản lý người dùng
                </a>
                <a href="#" class="nav-link" data-section="orders">
                    <i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div id="content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); saveProduct();">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Danh mục</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Giá</label>
                            <input type="number" class="form-control" id="price" name="price" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Số lượng</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" enctype="multipart/form-data">
                        <input type="hidden" id="editProductId" name="id">
                        <div class="mb-3">
                            <label for="editProductName" class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="editProductName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductCategory" class="form-label">Danh mục</label>
                            <select class="form-select" id="editProductCategory" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editProductPrice" class="form-label">Giá</label>
                            <input type="number" class="form-control" id="editProductPrice" name="price" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="editProductQuantity" class="form-label">Số lượng</label>
                            <input type="number" class="form-control" id="editProductQuantity" name="quantity" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="editProductStatus" class="form-label">Trạng thái</label>
                            <select class="form-select" id="editProductStatus" name="status">
                                <option value="1">Còn hàng</option>
                                <option value="0">Hết hàng</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            <div class="text-center mb-2">
                                <img id="currentProductImage" src="" alt="Current product image" 
                                     style="max-width: 200px; max-height: 200px; object-fit: contain;">
                            </div>
                            <label for="editProductImage" class="form-label">Thay đổi hình ảnh</label>
                            <input type="file" class="form-control" id="editProductImage" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="editProductDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="editProductDescription" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateProduct()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Tên danh mục</label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveCategory()">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryId" name="id">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Tên danh mục</label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateCategory()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên người dùng</label>
                            <input type="text" class="form-control" id="username" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="userPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="userPhone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="userPassword" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="userPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="userRole" class="form-label">Vai trò</label>
                            <select class="form-select" id="userRole" name="role">
                                <option value="user">Người dùng</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId" name="id">
                        <div class="mb-3">
                            <label for="editUserEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editUserEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUserUsername" class="form-label">Tên người dùng</label>
                            <input type="text" class="form-control" id="editUserUsername" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="editUserPhone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="editUserPhone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="editUserRole" class="form-label">Vai trò</label>
                            <select class="form-select" id="editUserRole" name="role">
                                <option value="user">Người dùng</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editUserPassword" class="form-label">Mật khẩu mới (để trống nếu không thay đổi)</label>
                            <input type="password" class="form-control" id="editUserPassword" name="password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div class="modal fade" id="editOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editOrderForm">
                        <input type="hidden" id="editOrderId" name="id">
                        <div class="mb-3">
                            <label for="editOrderStatus" class="form-label">Trạng thái đơn hàng</label>
                            <select class="form-select" id="editOrderStatus" name="status">
                                <option value="pending">Chờ xử lý</option>
                                <option value="processing">Đang xử lý</option>
                                <option value="shipped">Đã giao hàng</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateOrder()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('content');
        const navLinks = document.querySelectorAll('.nav-link');

        // Function to load content
        function loadContent(section) {
            fetch(`sections/${section}.php`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    // Re-initialize any scripts
                    const scripts = content.getElementsByTagName('script');
                    Array.from(scripts).forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = script.textContent;
                        script.parentNode.replaceChild(newScript, script);
                    });
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <h4>Error loading content</h4>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }

        // Add click event listeners to nav links
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                link.classList.add('active');
                // Load content
                const section = link.getAttribute('data-section');
                loadContent(section);
            });
        });

        // Load dashboard by default
        loadContent('dashboard');
    });
    </script>
</body>
</html> 