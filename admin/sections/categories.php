<?php
require_once '../config.php';
require_once '../../app/config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách categories
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
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
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="fas fa-plus"></i> Thêm danh mục
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr data-category-id="<?php echo $category['id']; ?>">
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal" onclick="setDeleteId(<?php echo $category['id']; ?>)">
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

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" id="categoryName" required>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa danh mục này không? Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="deleteCategory()">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toastNotification" class="toast align-items-center text-white bg-success" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let categoryToDelete = null;

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo toast notification
    const toastEl = document.getElementById('toastNotification');
    const toast = new bootstrap.Toast(toastEl);
});

function setDeleteId(id) {
    categoryToDelete = id;
}

async function deleteCategory() {
    if (!categoryToDelete) return;

    try {
        const response = await fetch('api/categories.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: parseInt(categoryToDelete) })
        });

        const result = await response.json();
        
        // Đóng modal xác nhận
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        modal.hide();

        if (result.success) {
            // Hiển thị thông báo thành công
            showNotification('Xóa danh mục thành công');
            
            // Reload trang sau 1 giây
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.message || 'Có lỗi xảy ra khi xóa danh mục', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi xóa danh mục', 'danger');
    } finally {
        categoryToDelete = null;
    }
}

async function editCategory(id) {
    try {
        const response = await fetch(`api/categories.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('categoryModalTitle').textContent = 'Chỉnh sửa danh mục';
            document.getElementById('categoryId').value = result.category.id;
            document.getElementById('categoryName').value = result.category.name;
            
            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        } else {
            showNotification(result.message || 'Có lỗi xảy ra khi lấy thông tin danh mục', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi lấy thông tin danh mục', 'danger');
    }
}

function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('toastNotification');
    const messageEl = document.getElementById('toastMessage');
    
    toastEl.classList.remove('bg-success', 'bg-danger');
    toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
    
    messageEl.textContent = message;
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

async function saveCategory() {
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('categoryName').value;
                
    if (!name) {
        showNotification('Vui lòng nhập tên danh mục', 'danger');
        return;
}

    try {
        const response = await fetch('api/categories.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id || null,
                name: name
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Đóng modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('categoryModal'));
            modal.hide();
            
            // Hiển thị thông báo thành công
            showNotification(id ? 'Cập nhật danh mục thành công' : 'Thêm danh mục thành công');
            
            // Reload trang
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.message || 'Có lỗi xảy ra khi lưu danh mục', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi lưu danh mục', 'danger');
    }
}
</script> 