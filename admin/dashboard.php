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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopCart</title>
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
        }
        .section.active {
            display: block;
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
                <a href="index.php">
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
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Danh mục</label>
                            <select class="form-select" id="productCategory" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Giá</label>
                            <input type="number" class="form-control" id="productPrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label">Số lượng</label>
                            <input type="number" class="form-control" id="productQuantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="productImage" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveProduct()">Lưu</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global loading functions
        function showLoading() {
            document.getElementById('loadingSpinner').classList.remove('d-none');
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').classList.add('d-none');
        }

        // Function to load section content
        async function loadSection(sectionName) {
            if (sectionName === 'dashboard') {
                document.querySelectorAll('.section').forEach(section => {
                    section.classList.remove('active');
                    if (section.id === 'dashboard') {
                        section.classList.add('active');
                    }
                });
                return;
            }

            try {
                showLoading();
                const response = await fetch(`sections/${sectionName}.php`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                
                if (data.success) {
                    const targetSection = document.getElementById(sectionName);
                    targetSection.innerHTML = data.html;
                    document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
                    targetSection.classList.add('active');
                } else {
                    throw new Error(data.message || 'Có lỗi xảy ra khi tải dữ liệu');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Có lỗi xảy ra khi tải dữ liệu');
            } finally {
                hideLoading();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');

            // Handle navigation
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    
                    // Update active state
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');

                    // Load section content
                    loadSection(section);

                    // Update URL hash
                    window.location.hash = section;
                });
            });

            // Handle initial load based on URL hash
            const initialSection = window.location.hash.slice(1) || 'dashboard';
            const initialLink = document.querySelector(`[data-section="${initialSection}"]`);
            if (initialLink) {
                initialLink.classList.add('active');
                loadSection(initialSection);
            }
        });

        // Product management functions
        async function editProduct(productId) {
            try {
                showLoading();
                const response = await fetch(`sections/get_product.php?id=${productId}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                console.log('Product data:', data); // Debug log

                if (data.success) {
                    // Populate edit form
                    const form = document.getElementById('editProductForm');
                    const product = data.product;

                    document.getElementById('editProductId').value = product.id;
                    document.getElementById('editProductName').value = product.name;
                    document.getElementById('editProductCategory').value = product.category_id;
                    document.getElementById('editProductPrice').value = product.price;
                    document.getElementById('editProductQuantity').value = product.quantity || 0;
                    document.getElementById('editProductStatus').value = product.status || 0;
                    document.getElementById('editProductDescription').value = product.description || '';
                    
                    // Update image preview
                    const imageUrl = product.image ? 
                        `../uploads/products/${product.image}` : 
                        '../uploads/products/default.jpg';
                    document.getElementById('currentProductImage').src = imageUrl;
                    
                    // Show edit modal
                    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                    editModal.show();
                } else {
                    throw new Error(data.message || 'Không thể tải thông tin sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Có lỗi xảy ra khi tải thông tin sản phẩm');
            } finally {
                hideLoading();
            }
        }

        async function updateProduct() {
            const form = document.getElementById('editProductForm');
            const formData = new FormData(form);

            try {
                showLoading();
                const response = await fetch('actions/update_product.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                console.log('Server response:', data); // Debug log

                if (data.success) {
                    // Đóng modal
                    const modal = document.getElementById('editProductModal');
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                    
                    // Reset form
                    form.reset();
                    
                    // Hiển thị thông báo thành công
                    alert('Cập nhật sản phẩm thành công!');
                    
                    // Tải lại danh sách sản phẩm
                    await loadSection('products');
                } else {
                    throw new Error(data.message || 'Không thể cập nhật sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Có lỗi xảy ra khi cập nhật sản phẩm');
            } finally {
                hideLoading();
            }
        }

        async function deleteProduct(productId) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                return;
            }

            try {
                showLoading();
                const response = await fetch('actions/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                if (data.success) {
                    alert('Xóa sản phẩm thành công');
                    loadSection('products');
                } else {
                    throw new Error(data.message || 'Không thể xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'Có lỗi xảy ra khi xóa sản phẩm');
            } finally {
                hideLoading();
            }
        }

        // Category management functions
        async function saveCategory() {
            const form = document.getElementById('addCategoryForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('actions/add_category.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('Thêm danh mục thành công!');
                    bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                    loadSection('categories');
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi thêm danh mục');
                }
            } catch (error) {
                alert('Có lỗi xảy ra khi thêm danh mục');
            }
        }

        async function editCategory(id) {
            // Implement edit category functionality
            console.log('Edit category:', id);
        }

        async function deleteCategory(id) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                try {
                    const response = await fetch('actions/delete_category.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Xóa danh mục thành công!');
                        loadSection('categories');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa danh mục');
                    }
                } catch (error) {
                    alert('Có lỗi xảy ra khi xóa danh mục');
                }
            }
        }

        // Update loadContent function to use loading spinner
        async function loadContent(section) {
            try {
                showLoading();
                const response = await fetch(`sections/${section}.php`);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                
                // Hide all sections
                document.querySelectorAll('.section').forEach(el => el.classList.add('d-none'));
                
                // Show selected section
                const sectionElement = document.getElementById(`${section}Section`);
                if (sectionElement) {
                    sectionElement.classList.remove('d-none');
                    if (data.success) {
                        sectionElement.innerHTML = data.html;
                    } else {
                        sectionElement.innerHTML = `<div class="alert alert-danger">${data.message || 'Có lỗi xảy ra khi tải dữ liệu'}</div>`;
                    }
                }
                
                // Update active state in sidebar
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-section') === section) {
                        link.classList.add('active');
                    }
                });
                
                // Update URL without page reload
                history.pushState({section: section}, '', `?section=${section}`);
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải nội dung');
            } finally {
                hideLoading();
            }
        }
    </script>
</body>
</html> 