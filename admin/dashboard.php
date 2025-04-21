<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once 'auth.php';

// Check admin authentication
checkAdminAuth();

// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn()
];

// Get categories for product form
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

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
                <a href="#dashboard" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="#products" class="nav-link" data-section="products">
                    <i class="fas fa-box me-2"></i> Quản lý sản phẩm
                </a>
                <a href="#categories" class="nav-link" data-section="categories">
                    <i class="fas fa-tags me-2"></i> Quản lý danh mục
                </a>
                <a href="#users" class="nav-link" data-section="users">
                    <i class="fas fa-users me-2"></i> Quản lý người dùng
                </a>
                <a href="#orders" class="nav-link" data-section="orders">
                    <i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Dashboard Section -->
                <div id="dashboard" class="section active">
                    <h2 class="mb-4">Dashboard</h2>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card bg-primary">
                                <i class="fas fa-box"></i>
                                <h3><?php echo $stats['products']; ?></h3>
                                <p>Sản phẩm</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-success">
                                <i class="fas fa-tags"></i>
                                <h3><?php echo $stats['categories']; ?></h3>
                                <p>Danh mục</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-info">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $stats['users']; ?></h3>
                                <p>Người dùng</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-warning">
                                <i class="fas fa-shopping-cart"></i>
                                <h3><?php echo $stats['orders']; ?></h3>
                                <p>Đơn hàng</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Đơn hàng gần đây</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày đặt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->query("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
                                        while ($order = $stmt->fetch()) {
                                            echo "<tr>";
                                            echo "<td>#" . $order['id'] . "</td>";
                                            echo "<td>" . htmlspecialchars($order['email']) . "</td>";
                                            echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . "đ</td>";
                                            echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div id="products" class="section"></div>

                <!-- Categories Section -->
                <div id="categories" class="section"></div>

                <!-- Users Section -->
                <div id="users" class="section"></div>

                <!-- Orders Section -->
                <div id="orders" class="section"></div>
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
                            <select class="form-select" id="userRole" name="is_admin">
                                <option value="0">Người dùng</option>
                                <option value="1">Admin</option>
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
                            <select class="form-select" id="editUserRole" name="is_admin">
                                <option value="0">Người dùng</option>
                                <option value="1">Admin</option>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Lưu thông tin session vào biến JavaScript
        const sessionData = <?php echo json_encode($sessionData); ?>;
        
        // Kiểm tra session
        function checkSession() {
            if (!sessionData.user_id || sessionData.role !== 'admin') {
                console.error('Session không hợp lệ, chuyển hướng đến trang đăng nhập');
                window.location.href = '../login.php';
                return false;
            }
            return true;
        }
        
        // Global state
        let currentSection = 'dashboard';
        let isLoading = false;
        let dataTables = {};
        let lastLoadTime = {};
        let loadAttempts = {};

        // Function to show/hide loading state
        function setLoadingState(sectionElement, loading) {
            if (loading) {
                sectionElement.classList.add('section-loading');
            } else {
                sectionElement.classList.remove('section-loading');
            }
        }

        // Function to switch sections
        function switchSection(sectionId) {
            // Ẩn tất cả các section
            document.querySelectorAll('.section').forEach(s => {
                s.classList.remove('active');
                setTimeout(() => {
                    s.style.display = 'none';
                }, 300);
            });
            
            // Hiển thị section được chọn
            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.style.display = 'block';
                // Đợi một chút để đảm bảo display: block đã được áp dụng
                setTimeout(() => {
                    selectedSection.classList.add('active');
                }, 50);
            }
            
            // Cập nhật active state cho menu
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }

        // Function to initialize DataTables
        function initializeDataTables() {
            // Hủy các DataTable cũ nếu có
            Object.values(dataTables).forEach(table => {
                if (table) {
                    table.destroy();
                }
            });
            dataTables = {};

            // Khởi tạo DataTable cho bảng sản phẩm nếu có
            const productsTable = document.getElementById('productsTable');
            if (productsTable) {
                dataTables.products = new DataTable(productsTable, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }

            // Khởi tạo DataTable cho bảng danh mục nếu có
            const categoriesTable = document.getElementById('categoriesTable');
            if (categoriesTable) {
                dataTables.categories = new DataTable(categoriesTable, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }

            // Khởi tạo DataTable cho bảng người dùng nếu có
            const usersTable = document.getElementById('usersTable');
            if (usersTable) {
                dataTables.users = new DataTable(usersTable, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }

            // Khởi tạo DataTable cho bảng đơn hàng nếu có
            const ordersTable = document.getElementById('ordersTable');
            if (ordersTable) {
                dataTables.orders = new DataTable(ordersTable, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }
        }

        // Function to load section content
        async function loadSection(section) {
            // Kiểm tra session trước khi tải
            if (!checkSession()) return;
            
            // Kiểm tra nếu đang tải
            if (isLoading) {
                console.log('Đang tải section khác, bỏ qua yêu cầu tải section:', section);
                return;
            }
            
            // Kiểm tra thời gian tải lại
            const now = Date.now();
            if (lastLoadTime[section] && (now - lastLoadTime[section] < 2000)) {
                console.log('Tải lại quá nhanh, bỏ qua yêu cầu tải section:', section);
                return;
            }
            
            // Kiểm tra số lần tải
            if (loadAttempts[section] && loadAttempts[section] > 3) {
                console.log('Quá nhiều lần tải, bỏ qua yêu cầu tải section:', section);
                return;
            }
            
            try {
                currentSection = section;
                switchSection(section);
                
                // If dashboard, no need to load data
                if (section === 'dashboard') {
                    return;
                }

                const selectedSection = document.getElementById(section);
                if (!selectedSection) return;

                isLoading = true;
                setLoadingState(selectedSection, true);
                
                // Cập nhật thời gian tải và số lần tải
                lastLoadTime[section] = now;
                loadAttempts[section] = (loadAttempts[section] || 0) + 1;
                
                const response = await fetch(`sections/${section}.php`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache'
                    },
                    credentials: 'same-origin' // Thêm credentials để gửi cookies
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && currentSection === section) { // Check if section hasn't changed
                    selectedSection.innerHTML = data.html;
                    
                    // Khởi tạo DataTables sau khi nội dung được tải
                    setTimeout(() => {
                        initializeDataTables();
                    }, 100);
                    
                    // Reset số lần tải sau khi tải thành công
                    loadAttempts[section] = 0;
                } else if (!data.success) {
                    throw new Error(data.message || 'Có lỗi xảy ra khi tải dữ liệu');
                }
            } catch (error) {
                console.error('Error:', error);
                const errorMessage = error.message || 'Có lỗi xảy ra khi tải dữ liệu';
                showAlert('danger', errorMessage);
                
                const selectedSection = document.getElementById(section);
                if (selectedSection && currentSection === section) {
                    selectedSection.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            ${errorMessage}
                        </div>`;
                }
            } finally {
                isLoading = false;
                const selectedSection = document.getElementById(section);
                if (selectedSection) {
                    setLoadingState(selectedSection, false);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra session khi trang tải
            if (!checkSession()) return;
            
            // Khởi tạo Bootstrap tooltips và popovers
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Xử lý sự kiện click cho menu
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    if (section) {
                        window.location.hash = section;
                        loadSection(section);
                    }
                });
            });
            
            // Xử lý hash URL khi load trang
            const hash = window.location.hash.substring(1);
            const defaultSection = hash || 'dashboard';
            loadSection(defaultSection);
            
            // Xử lý sự kiện hashchange
            window.addEventListener('hashchange', function() {
                const newSection = window.location.hash.substring(1) || 'dashboard';
                if (newSection !== currentSection) {
                    loadSection(newSection);
                }
            });
            
            // Thêm event listener cho modal thêm sản phẩm
            const addProductModal = document.getElementById('addProductModal');
            if (addProductModal) {
                addProductModal.addEventListener('show.bs.modal', async function () {
                    try {
                        const response = await fetch('sections/get_categories.php');
                        const data = await response.json();
                        if (data.success) {
                            const select = document.getElementById('category_id');
                            select.innerHTML = '<option value="">Chọn danh mục</option>';
                            select.innerHTML += data.categories.map(category => 
                                `<option value="${category.id}">${category.name}</option>`
                            ).join('');
                        }
                    } catch (error) {
                        console.error('Error loading categories:', error);
                    }
                });
            }
        });

        // Function to show alert messages
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Cập nhật các hàm xử lý sản phẩm
        async function saveProduct() {
            // Kiểm tra session trước khi thực hiện
            if (!checkSession()) return;
            
            const form = document.getElementById('addProductForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            try {
                const response = await fetch('actions/add_product.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Thêm credentials để gửi cookies
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    // Đóng modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                    if (modal) {
                        modal.hide();
                    } else {
                        document.getElementById('addProductModal').style.display = 'none';
                        document.querySelector('.modal-backdrop').remove();
                        document.body.classList.remove('modal-open');
                    }

                    // Reset form
                    form.reset();

                    // Hiển thị thông báo thành công
                    showAlert('success', 'Thêm sản phẩm thành công!');

                    // Tải lại danh sách sản phẩm
                    await loadSection('products');
                } else {
                    showAlert('danger', result.message || 'Có lỗi xảy ra khi thêm sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'Có lỗi xảy ra khi thêm sản phẩm: ' + error.message);
            }
        }

        async function updateProduct() {
            // Kiểm tra session trước khi thực hiện
            if (!checkSession()) return;
            
            const form = document.getElementById('editProductForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('actions/update_product.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Thêm credentials để gửi cookies
                });

                const data = await response.json();
                if (data.success) {
                    // Đóng modal
                    const modal = document.getElementById('editProductModal');
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                    
                    // Reset form
                    form.reset();
                    
                    // Hiển thị thông báo thành công
                    showAlert('success', 'Cập nhật sản phẩm thành công!');
                    
                    // Tải lại danh sách sản phẩm
                    await loadSection('products');
                } else {
                    throw new Error(data.message || 'Không thể cập nhật sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', error.message || 'Có lỗi xảy ra khi cập nhật sản phẩm');
            }
        }

        async function deleteProduct(productId) {
            // Kiểm tra session trước khi thực hiện
            if (!checkSession()) return;
            
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                return;
            }

            try {
                const response = await fetch('actions/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId }),
                    credentials: 'same-origin' // Thêm credentials để gửi cookies
                });

                const data = await response.json();
                if (data.success) {
                    showAlert('success', 'Xóa sản phẩm thành công');
                    await loadSection('products');
                } else {
                    throw new Error(data.message || 'Không thể xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', error.message || 'Có lỗi xảy ra khi xóa sản phẩm');
            }
        }

        // ... rest of your existing code ...
    </script>
</body>
</html> 