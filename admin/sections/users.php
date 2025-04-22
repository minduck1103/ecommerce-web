<?php
require_once '../config.php';
require_once '../../app/config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Lấy danh sách người dùng
try {
    $stmt = $conn->query("SELECT id, username, email, full_name, address, phone, role, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<!-- Modal thêm/sửa người dùng -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password">
                        <small class="text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                        </div>
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="fullName">
                        </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò</label>
                        <select class="form-select" id="role">
                            <option value="user">Người dùng</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Lưu</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS styles -->
<style>
.action-column {
    text-align: center !important;
    vertical-align: middle !important;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
}

/* Custom Dialog Styles */
.custom-dialog-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
}

.custom-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    z-index: 1051;
    min-width: 320px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.custom-dialog-message {
    margin-bottom: 20px;
    font-size: 16px;
    text-align: center;
}

.custom-dialog-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* Toast Styles */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1060;
}

.success-toast {
    background-color: #198754;
    color: white;
}
</style>

<!-- Bảng danh sách người dùng -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Quản lý người dùng</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Danh sách người dùng
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Họ và tên</th>
                            <th>Địa chỉ</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Ngày tạo</th>
                            <th class="action-column" style="width: 80px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['address'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            <td class="action-column">
                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                <button type="button" class="btn btn-sm btn-danger btn-action" onclick="confirmDelete(<?= $user['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Dialog -->
<div class="custom-dialog-overlay" id="deleteConfirmDialog">
    <div class="custom-dialog">
        <div class="custom-dialog-message">
            Bạn có chắc chắn muốn xóa người dùng này?
        </div>
        <div class="custom-dialog-buttons">
            <button type="button" class="btn btn-primary" onclick="handleDeleteConfirm()">Có</button>
            <button type="button" class="btn btn-secondary" onclick="hideDeleteDialog()">Không</button>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="toast-container">
    <div class="toast success-toast" id="successToast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            Xóa dữ liệu người dùng thành công
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo DataTable
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Khởi tạo toast
    const successToast = new bootstrap.Toast(document.getElementById('successToast'), {
        delay: 3000
    });
    window.successToast = successToast;
});

function confirmDelete(id) {
    if (!id) {
        console.error('No user ID provided');
        return;
    }

    document.getElementById('deleteConfirmDialog').style.display = 'block';
    window.userIdToDelete = id;
}

function hideDeleteDialog() {
    document.getElementById('deleteConfirmDialog').style.display = 'none';
    window.userIdToDelete = null;
}

function handleDeleteConfirm() {
    const id = window.userIdToDelete;
    if (!id) {
        console.error('No user ID to delete');
        return;
    }
    
    deleteUser(id);
    hideDeleteDialog();
}

function deleteUser(id) {
    fetch('../api/users.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id }),
        credentials: 'include' // Đảm bảo gửi cookies
    })
    .then(response => response.text()) // Đọc response dưới dạng text trước
    .then(text => {
        try {
            return JSON.parse(text); // Thử parse JSON
        } catch (e) {
            console.error('Response text:', text);
            if (text.includes('<!DOCTYPE') || text.includes('Unauthorized')) {
                window.location.href = '../login.php';
                throw new Error('Phiên làm việc đã hết hạn');
            }
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        if (data.success) {
            // Hiển thị toast thành công
            window.successToast.show();
            
            // Đợi 1 giây trước khi reload
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi xóa người dùng');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        if (error.message === 'Phiên làm việc đã hết hạn') {
            alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            window.location.href = '../login.php';
        } else {
            alert(error.message || 'Có lỗi xảy ra khi xóa người dùng. Vui lòng thử lại sau.');
        }
    });
}
</script> 