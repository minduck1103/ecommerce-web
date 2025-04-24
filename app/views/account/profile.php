<?php
require_once __DIR__ . '/../../config/database.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /shoppingcart/auth/login');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #333;
            --secondary-color: #666;
            --accent-color: #888;
            --background-color: #f8f9fa;
            --border-color: #dee2e6;
        }

        .profile-section {
            padding: 3rem 0;
            background-color: var(--background-color);
            min-height: calc(100vh - 100px);
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(to right, #333, #666);
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            border: 4px solid white;
            overflow: hidden;
            position: relative;
            background: white;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.6);
            padding: 0.5rem;
            color: white;
            font-size: 0.8rem;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-avatar:hover .avatar-upload {
            opacity: 1;
        }

        .profile-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .profile-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
        }

        .profile-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(136,136,136,0.25);
        }

        .btn-save {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-save:hover {
            background-color: rgba(136, 136, 136, 0.15);
            color: var(--accent-color);
            transform: translateY(-2px);
            border: 1px solid var(--accent-color);
        }

        .change-password-link {
            display: block;
            text-align: center;
            color: var(--accent-color);
            text-decoration: none;
            transition: color 0.3s ease;
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            padding: 0.5rem;
            margin-top: 0;
        }

        .change-password-link:hover {
            color: var(--primary-color);
            background-color: rgba(136, 136, 136, 0.1);
            border-radius: 8px;
        }

        .form-text {
            color: var(--secondary-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .profile-container {
                margin: 1rem;
            }

            .profile-header {
                padding: 1.5rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .profile-form {
                padding: 1.5rem;
            }
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        #changePasswordForm .form-control {
            font-size: 0.9rem;
        }

        #changePasswordForm .btn-save {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        /* Đảm bảo dropdown menu hiển thị đúng */
        .dropdown-menu.show {
            display: block;
            margin-top: 0.5rem;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: none;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?= !empty($user['avatar']) ? '/shoppingcart/public/uploads/avatars/' . $user['avatar'] : '/shoppingcart/public/images/default-avatar.jpg' ?>" 
                         alt="Avatar" 
                         id="avatarPreview">
                    <label for="avatarInput" class="avatar-upload">
                        <i class="fas fa-camera"></i> Thay đổi
                    </label>
                    <input type="file" id="avatarInput" style="display: none" accept="image/*">
                </div>
                <h1 class="profile-title">Thông tin cá nhân</h1>
                <p class="profile-subtitle">Cập nhật thông tin của bạn</p>
            </div>

            <form class="profile-form" id="profileForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <small class="form-text">Email không thể thay đổi</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Giới tính</label>
                            <select class="form-control" name="gender">
                                <option value="">Chọn giới tính</option>
                                <option value="male" <?= ($user['gender'] === 'male') ? 'selected' : '' ?>>Nam</option>
                                <option value="female" <?= ($user['gender'] === 'female') ? 'selected' : '' ?>>Nữ</option>
                                <option value="other" <?= ($user['gender'] === 'other') ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ</label>
                    <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-save mb-2">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </form>

            <!-- Form đổi mật khẩu -->
            <div>
                <button type="button" class="change-password-link" data-bs-toggle="collapse" data-bs-target="#changePasswordCollapse" aria-expanded="false" aria-controls="changePasswordCollapse">
                    <i class="fas fa-lock me-1"></i>Đổi mật khẩu
                </button>
                
                <div class="collapse mt-3" id="changePasswordCollapse">
                    <div class="card card-body">
                        <form id="changePasswordForm" onsubmit="return handleChangePassword(event)">
                            <div class="form-group mb-3">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-save">Lưu mật khẩu mới</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý upload avatar
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('avatar', file);

                fetch('/shoppingcart/app/api/account/update-avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('avatarPreview').src = data.avatar_url;
                        showToast('Cập nhật ảnh đại diện thành công', 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Có lỗi xảy ra khi cập nhật ảnh đại diện', 'error');
                });
            }
        });

        // Xử lý cập nhật thông tin
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/shoppingcart/app/api/account/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Cập nhật thông tin thành công', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi cập nhật thông tin', 'error');
            });
        });

        // Xử lý đổi mật khẩu
        function handleChangePassword(event) {
            event.preventDefault();
            console.log('Handling password change...');
            
            const form = event.target;
            const formData = new FormData(form);
            
            fetch('/shoppingcart/app/api/account/change-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Password change response:', data);
                if (data.success) {
                    showToast('Đổi mật khẩu thành công', 'success');
                    form.reset();
                    // Đóng collapse
                    const collapse = bootstrap.Collapse.getInstance(document.getElementById('changePasswordCollapse'));
                    if (collapse) {
                        collapse.hide();
                    }
                    // Reload trang sau 1 giây
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi đổi mật khẩu', 'error');
            });
            
            return false;
        }

        // Hiển thị thông báo
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast show position-fixed bottom-0 end-0 m-3`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                    <strong class="me-auto">Thông báo</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            document.body.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html> 