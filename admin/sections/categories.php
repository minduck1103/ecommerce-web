<?php
require_once '../config.php';
require_once '../../app/config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách danh mục
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Quản lý danh mục</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus me-2"></i>Thêm danh mục
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th>Số sản phẩm</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        // Đếm số sản phẩm trong danh mục
                        try {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                            $stmt->execute([$category['id']]);
                            $productCount = $stmt->fetchColumn();
                        } catch (PDOException $e) {
                            $productCount = 0;
                        }
                        ?>
                        <tr data-category-id="<?php echo $category['id']; ?>">
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo htmlspecialchars($category['description'] ?: 'Không có mô tả'); ?></td>
                            <td><?php echo $productCount; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" onclick="editCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" name="id" id="categoryId">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="categorySlug" required>
                        <small class="text-muted">Ví dụ: quan-ao-nam</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">Lưu</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toastNotification" class="toast align-items-center text-white bg-success" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let categoryModal;
let toast;

document.addEventListener('DOMContentLoaded', function() {
    categoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    toast = new bootstrap.Toast(document.getElementById('toastNotification'));
});

// Tự động tạo slug khi nhập tên danh mục
document.querySelector('#categoryName').addEventListener('input', function(e) {
    const slug = e.target.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    document.querySelector('#categorySlug').value = slug;
});

function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('toastNotification');
    const messageEl = document.getElementById('toastMessage');
    
    toastEl.classList.remove('bg-success', 'bg-danger');
    toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
    messageEl.textContent = message;
    
    const bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();
}

function editCategory(id) {
    console.log('Editing category with ID:', id);
    // Fetch category data
    fetch(`api/categories.php?id=${id}`)
        .then(response => {
            console.log('API Response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Category data:', data);
            if (data.success) {
                document.getElementById('categoryId').value = data.category.id;
                document.getElementById('categoryName').value = data.category.name;
                document.getElementById('categorySlug').value = data.category.slug;
                document.getElementById('categoryDescription').value = data.category.description || '';
                
                document.getElementById('categoryModalTitle').textContent = 'Chỉnh sửa danh mục';
                const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
                modal.show();
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error details:', error);
            showNotification('Có lỗi xảy ra khi tải thông tin danh mục', 'error');
        });
}

function deleteCategory(id) {
    if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
        console.log('Deleting category with ID:', id);
        fetch('api/categories.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => {
            console.log('Delete response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Delete result:', data);
            if (data.success) {
                showNotification('Xóa danh mục thành công');
                // Reload the page after successful deletion
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error details:', error);
            showNotification('Có lỗi xảy ra khi xóa danh mục', 'error');
        });
    }
}

function updateTableRow(category, productCount = 0) {
    // Tìm row cần update hoặc tạo row mới
    let row = document.querySelector(`tr[data-category-id="${category.id}"]`);
    const isNewCategory = !row;
    
    if (isNewCategory) {
        row = document.createElement('tr');
        row.setAttribute('data-category-id', category.id);
        document.querySelector('tbody').appendChild(row);
    }
    
    // Cập nhật nội dung row
    row.innerHTML = `
        <td>${category.id}</td>
        <td>${escapeHtml(category.name)}</td>
        <td>${escapeHtml(category.slug)}</td>
        <td>${escapeHtml(category.description || 'Không có mô tả')}</td>
        <td>${productCount}</td>
        <td>
            <button class="btn btn-sm btn-primary me-1" onclick="editCategory(${category.id})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
}

// Hàm escape HTML để tránh XSS
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function saveCategory() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const data = {
        name: formData.get('name'),
        slug: formData.get('slug'),
        description: formData.get('description')
    };
    
    if (id) {
        data.id = id;
    }

    console.log('Saving category data:', data);
    fetch('api/categories.php', {
        method: id ? 'PUT' : 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Save response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Save result:', data);
        if (data.success) {
            showNotification(id ? 'Cập nhật danh mục thành công' : 'Thêm danh mục thành công');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
            modal.hide();
            
            // Cập nhật UI mà không reload trang
            if (id) {
                // Trường hợp cập nhật
                updateTableRow({
                    id: id,
                    name: formData.get('name'),
                    slug: formData.get('slug'),
                    description: formData.get('description')
                });
            } else {
                // Trường hợp thêm mới
                fetch(`api/categories.php?id=${data.categoryId}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            updateTableRow(result.category);
                        }
                    });
            }
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Save error details:', error);
        showNotification('Có lỗi xảy ra khi lưu danh mục', 'error');
    });
}

// Thêm data-category-id vào các row khi trang được load
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const categoryId = row.querySelector('td:first-child').textContent;
        row.setAttribute('data-category-id', categoryId);
    });
});

// Reset form when modal is closed
document.getElementById('addCategoryModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModalTitle').textContent = 'Thêm danh mục mới';
});
</script> 