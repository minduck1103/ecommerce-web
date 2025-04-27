<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: /shoppingcart');
    exit;
}

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="auth-wrapper">
    <div class="register-container">
        <div class="register-header">
            <h1 class="register-title">Đăng ký tài khoản</h1>
            <p class="register-subtitle">Tạo tài khoản để mua sắm dễ dàng hơn!</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div id="registerToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <form id="registerForm" method="POST">
            <div class="form-floating">
                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Họ và tên" required>
                <label for="full_name">Họ và tên</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Số điện thoại" required>
                <label for="phone">Số điện thoại</label>
            </div>

            <div class="form-floating password-field">
                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                <label for="password">Mật khẩu</label>
                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                    <i class="far fa-eye"></i>
                </button>
            </div>

            <div class="form-floating password-field">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                    <i class="far fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus me-2"></i>
                Đăng ký
            </button>
        </form>

        <div class="social-register">
            <div class="social-register-title">
                <span>Hoặc đăng ký với</span>
            </div>
            <div class="social-buttons">
                <button class="social-btn" title="Đăng ký bằng Google">
                    <i class="fab fa-google"></i>
                </button>
                <button class="social-btn" title="Đăng ký bằng Facebook">
                    <i class="fab fa-facebook-f"></i>
                </button>
            </div>
        </div>

        <div class="register-footer">
            <p>Đã có tài khoản? <a href="/shoppingcart/auth/login">Đăng nhập ngay</a></p>
        </div>
    </div>
</div>

<style>
.auth-wrapper {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background-color: var(--background-color);
}

.register-container {
    background-color: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.register-subtitle {
    color: var(--text-muted);
    margin-bottom: 0;
}

.form-floating {
    margin-bottom: 1rem;
}

.form-floating > .form-control {
    padding: 1rem 0.75rem;
}

.password-field {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
}

.register-btn {
    width: 100%;
    padding: 0.75rem;
    background-color: #e9ecef; /* Màu xám nhạt */
    color: #333;
    border: none;
    border-radius: 5px;
    font-weight: 500;
    margin-top: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.register-btn:hover {
    background-color: #dee2e6; /* Màu xám đậm khi hover */
    transform: translateY(-1px);
}

.register-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.social-register {
    margin-top: 2rem;
    text-align: center;
}

.social-register-title {
    position: relative;
    margin-bottom: 1rem;
}

.social-register-title span {
    background-color: white;
    padding: 0 1rem;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.social-register-title::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: var(--border-color);
    z-index: -1;
}

.social-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.social-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--border-color);
    background-color: white;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.social-btn:hover {
    background-color: var(--background-color);
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.register-footer {
    margin-top: 2rem;
    text-align: center;
    color: var(--text-muted);
}

.register-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.register-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 576px) {
    .register-container {
        padding: 1.5rem;
    }

    .register-title {
        font-size: 1.5rem;
    }
}
</style>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        const formData = new FormData(this);

        fetch('/shoppingcart/app/api/auth/register.php', {
                method: 'POST',
                body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                const toast = new bootstrap.Toast(document.getElementById('registerToast'));
                document.getElementById('registerToast').classList.add('bg-success');
                document.querySelector('#registerToast .toast-body').textContent = data.message;
                toast.show();
                
                // Chờ 1 giây rồi chuyển hướng
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            } else {
                // Hiển thị thông báo lỗi
                const toast = new bootstrap.Toast(document.getElementById('registerToast'));
                document.getElementById('registerToast').classList.add('bg-danger');
                document.querySelector('#registerToast .toast-body').textContent = data.message;
                toast.show();
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hiển thị thông báo lỗi
            const toast = new bootstrap.Toast(document.getElementById('registerToast'));
            document.getElementById('registerToast').classList.add('bg-danger');
            document.querySelector('#registerToast .toast-body').textContent = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        toast.show();
            submitButton.disabled = false;
        });
    });
</script>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?> 