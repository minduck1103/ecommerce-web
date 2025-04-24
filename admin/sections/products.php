<?php
// Start session and include database first
session_start();
require_once __DIR__ . '/../../app/config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized access');
}

try {
    // Khởi tạo kết nối database
    $database = new Database();
    $conn = $database->getConnection();

    // Lấy danh sách sản phẩm
    $stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách danh mục cho form
    $categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Có lỗi xảy ra: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Quản lý sản phẩm</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </button>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Danh sách sản phẩm
            </div>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr data-product-id="<?php echo $product['id']; ?>">
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="/shoppingcart/public/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-thumbnail"
                                         onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                                <?php else: ?>
                                    <img src="/shoppingcart/public/images/default-product.jpg" 
                                         alt="No image"
                                         class="product-thumbnail">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td><?php echo number_format($product['price']); ?>đ</td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" onclick="editProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm/Sửa sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
                        <select class="form-select" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số lượng</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hình ảnh</label>
                        <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this)">
                        <div id="imagePreview" class="mt-2">
                            <img src="/shoppingcart/public/images/default-product.jpg" 
                                 alt="Product preview" 
                                 class="product-preview"
                                 style="display: none;">
                        </div>
                        <div id="currentImage" class="mt-2" style="display: none;">
                            <p class="mb-2">Hình ảnh hiện tại:</p>
                            <img src="" alt="Current image" class="product-preview">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Lưu</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa sản phẩm này?</p>
                <p class="text-danger"><small>Hành động này không thể hoàn tác.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<style>
.product-thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    transition: transform 0.2s ease;
}

.product-thumbnail:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.product-preview {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-top: 10px;
}

#imagePreview {
    text-align: center;
    margin-top: 10px;
}

/* Toast Styles */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 4px;
    padding: 16px 20px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 300px;
    max-width: 400px;
    animation: slideIn 0.3s ease;
}

.toast.success {
    border-left: 4px solid #4caf50;
}

.toast.error {
    border-left: 4px solid #f44336;
}

.toast.info {
    border-left: 4px solid #2196f3;
}

.toast i {
    font-size: 20px;
}

.toast.success i {
    color: #4caf50;
}

.toast.error i {
    color: #f44336;
}

.toast.info i {
    color: #2196f3;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>

<!-- Toast Container -->
<div class="toast-container"></div>

<script>
// Toast Implementation
const Toast = {
    container: document.querySelector('.toast-container'),

    show(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon;
        switch(type) {
            case 'success':
                icon = 'check-circle';
                break;
            case 'error':
                icon = 'exclamation-circle';
                break;
            case 'info':
                icon = 'info-circle';
                break;
            default:
                icon = 'bell';
        }
        
        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <div>${message}</div>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                this.container.removeChild(toast);
            }, 300);
        }, 3000);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý tìm kiếm sản phẩm
    const searchInput = document.getElementById('searchProduct');
    const productsTable = document.querySelector('.table-striped');
    
    if (searchInput && productsTable) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = productsTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let row of rows) {
                const productName = row.cells[2].textContent.toLowerCase();
                const categoryName = row.cells[3].textContent.toLowerCase();
                
                if (productName.includes(searchText) || categoryName.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Thêm data-product-id vào mỗi row trong bảng
    const rows = document.querySelectorAll('#productsTable tbody tr');
    rows.forEach(row => {
        const productId = row.cells[0].textContent;
        row.setAttribute('data-product-id', productId);
    });
});

function previewImage(input) {
    const preview = document.querySelector('#imagePreview img');
    const currentImage = document.getElementById('currentImage');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            currentImage.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.style.display = 'none';
        if (currentImage.querySelector('img').src) {
            currentImage.style.display = 'block';
        }
    }
}

function editProduct(id) {
    console.log('Calling API with ID:', id);
    
    fetch(`/shoppingcart/admin/api/products.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                const form = document.getElementById('productForm');
                
                // Reset form first
                form.reset();
                
                // Set form values
                form.querySelector('[name="id"]').value = product.id;
                form.querySelector('[name="name"]').value = product.name;
                form.querySelector('[name="category_id"]').value = product.category_id;
                form.querySelector('[name="price"]').value = product.price;
                form.querySelector('[name="quantity"]').value = product.quantity;
                form.querySelector('[name="description"]').value = product.description || '';
                form.querySelector('[name="action"]').value = 'update';
                
                // Show current image if exists
                const currentImageDiv = document.getElementById('currentImage');
                const imagePreview = document.querySelector('#imagePreview img');
                
                if (product.image) {
                    currentImageDiv.style.display = 'block';
                    currentImageDiv.querySelector('img').src = '/shoppingcart/public/uploads/products/' + product.image;
                    imagePreview.style.display = 'none';
                } else {
                    currentImageDiv.style.display = 'none';
                    imagePreview.style.display = 'none';
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('productModal'));
                modal.show();
            } else {
                alert(data.message || 'Không thể tải thông tin sản phẩm');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi tải thông tin sản phẩm');
        });
}

function deleteProduct(id) {
    // Lưu ID sản phẩm cần xóa
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    confirmDeleteBtn.setAttribute('data-product-id', id);
    
    // Hiển thị modal xác nhận
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Xử lý sự kiện khi nhấn nút xác nhận xóa
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    const productId = this.getAttribute('data-product-id');
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    
    // Hiển thị toast loading
    Toast.show('Đang xóa sản phẩm...', 'info');
    
        const formData = new FormData();
        formData.append('action', 'delete');
    formData.append('id', productId);
        
        fetch('/shoppingcart/admin/api/products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            // Đóng modal
            deleteModal.hide();
            
            // Xóa hàng khỏi bảng
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }
            
            // Hiển thị thông báo thành công
            Toast.success('Xóa sản phẩm thành công!');
        } else {
            throw new Error(data.message || 'Không thể xóa sản phẩm');
            }
        })
        .catch(error => {
        console.error('Delete Error:', error);
        Toast.error('Đã xảy ra lỗi khi xóa sản phẩm: ' + error.message);
    });
        });

function updateProductRow(product) {
    const row = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (row) {
        // Update existing row
        row.innerHTML = `
            <td>${product.id}</td>
            <td>
                ${product.image ? `
                    <img src="/shoppingcart/public/uploads/products/${product.image}" 
                         alt="${product.name}" 
                         class="product-thumbnail"
                         onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                ` : `
                    <img src="/shoppingcart/public/images/default-product.jpg" 
                         alt="No image"
                         class="product-thumbnail">
                `}
            </td>
            <td>${product.name}</td>
            <td>${product.category_name}</td>
            <td>${Number(product.price).toLocaleString()}đ</td>
            <td>${product.quantity}</td>
            <td>
                <button class="btn btn-sm btn-primary me-1" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
    } else {
        // Add new row
        const tbody = document.querySelector('#productsTable tbody');
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-product-id', product.id);
        newRow.innerHTML = `
            <td>${product.id}</td>
            <td>
                ${product.image ? `
                    <img src="/shoppingcart/public/uploads/products/${product.image}" 
                         alt="${product.name}" 
                         class="product-thumbnail"
                         onerror="this.src='/shoppingcart/public/images/default-product.jpg'">
                ` : `
                    <img src="/shoppingcart/public/images/default-product.jpg" 
                         alt="No image"
                         class="product-thumbnail">
                `}
            </td>
            <td>${product.name}</td>
            <td>${product.category_name}</td>
            <td>${Number(product.price).toLocaleString()}đ</td>
            <td>${product.quantity}</td>
            <td>
                <button class="btn btn-sm btn-primary me-1" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.insertBefore(newRow, tbody.firstChild);
    }
}

function saveProduct() {
    const form = document.getElementById('productForm');
    const formData = new FormData(form);
    const isUpdate = formData.get('action') === 'update';
    
    // Hiển thị loading toast
    Toast.show('Đang xử lý...', 'info');
    
    fetch('/shoppingcart/admin/api/products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('API Response:', data); // Debug log
        
        if (data.success) {
            // Đóng modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
            modal.hide();

            if (isUpdate && data.product) {
                // Nếu là cập nhật và có dữ liệu sản phẩm trả về
                updateProductRow(data.product);
                Toast.success('Cập nhật sản phẩm thành công!');
            } else if (data.product_id) {
                // Nếu là thêm mới và có ID sản phẩm trả về
                fetch(`/shoppingcart/admin/api/products.php?action=get&id=${data.product_id}`)
                    .then(response => response.json())
                    .then(productData => {
                        console.log('Get Product Response:', productData); // Debug log
                        if (productData.success) {
                            updateProductRow(productData.product);
                            Toast.success('Thêm sản phẩm thành công!');
                        } else {
                            Toast.error(productData.message || 'Không thể tải thông tin sản phẩm');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching new product:', error);
                        Toast.error('Lỗi khi tải thông tin sản phẩm mới');
                    });
            } else {
                Toast.success(data.message || 'Thao tác thành công!');
            }

            // Reset form
            form.reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('currentImage').style.display = 'none';
        } else {
            Toast.error(data.message || 'Không thể lưu sản phẩm');
        }
    })
    .catch(error => {
        console.error('Save Product Error:', error);
        Toast.error('Đã xảy ra lỗi: ' + error.message);
    });
}

// Reset form khi mở modal thêm mới
document.getElementById('productModal').addEventListener('show.bs.modal', function (event) {
    if (!event.relatedTarget) return; // Skip if modal is shown programmatically
    
    const form = document.getElementById('productForm');
    form.reset();
    form.querySelector('[name="action"]').value = 'create';
    form.querySelector('[name="id"]').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('currentImage').style.display = 'none';
});
</script> 