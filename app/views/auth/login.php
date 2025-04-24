<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: /shoppingcart');
    exit;
}

// Lấy URL redirect sau khi đăng nhập thành công (nếu có)
$redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '/shoppingcart';

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="auth-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h1 class="login-title">Đăng nhập</h1>
            <p class="login-subtitle">Chào mừng bạn quay trở lại!</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div id="loginToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <form id="loginForm" method="POST">
            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirect_url) ?>">
            
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating password-field">
                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                <label for="password">Mật khẩu</label>
                <button type="button" class="toggle-password" onclick="togglePassword()">
                    <i class="far fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt me-2"></i>
                Đăng nhập
            </button>
        </form>

        <div class="social-login">
            <div class="social-login-title">
                <span>Hoặc đăng nhập với</span>
            </div>
            <div class="social-buttons">
                <button class="social-btn" title="Đăng nhập bằng Google">
                    <i class="fab fa-google"></i>
                </button>
                <button class="social-btn" title="Đăng nhập bằng Facebook">
                    <i class="fab fa-facebook-f"></i>
                </button>
            </div>
        </div>

        <div class="login-footer">
            <p>Chưa có tài khoản? <a href="/shoppingcart/auth/register">Đăng ký ngay</a></p>
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

:root {
    --primary-color: #333;
    --secondary-color: #666;
    --background-color: #f8f9fa;
    --border-color: #ddd;
    --input-bg: #fff;
    --input-border: #e0e0e0;
    --button-hover: #222;
}

body {
    padding: 2rem 1rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.login-container {
    width: 100%;
    max-width: 400px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: var(--secondary-color);
    font-size: 0.95rem;
}

.form-floating {
    margin-bottom: 1.25rem;
}

.form-floating > .form-control {
    padding: 1rem 1rem;
    height: calc(3.5rem + 2px);
    border: 1px solid var(--input-border);
    border-radius: 8px;
    font-size: 1rem;
    background-color: var(--input-bg);
    transition: all 0.3s ease;
}

.form-floating > .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(51, 51, 51, 0.1);
}

.form-floating > label {
    padding: 1rem;
    color: var(--secondary-color);
}

.password-field {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: none;
    color: var(--secondary-color);
    cursor: pointer;
    z-index: 10;
}

.login-btn {
    width: 100%;
    padding: 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.login-btn:hover {
    background: var(--button-hover);
    transform: translateY(-1px);
}

.login-footer {
    text-align: center;
    margin-top: 2rem;
    color: var(--secondary-color);
}

.login-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.login-footer a:hover {
    color: var(--button-hover);
}

.social-login {
    margin-top: 2rem;
    text-align: center;
}

.social-login-title {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    color: var(--secondary-color);
}

.social-login-title::before,
.social-login-title::after {
    content: "";
    flex: 1;
    border-top: 1px solid var(--border-color);
}

.social-login-title span {
    padding: 0 1rem;
    font-size: 0.9rem;
}

.social-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.social-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 1px solid var(--border-color);
    background: white;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.social-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.alert {
    border-radius: 8px;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 480px) {
    .login-container {
        padding: 1.5rem;
    }
    
    .login-title {
        font-size: 1.5rem;
    }
}
</style>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.classList.remove('fa-eye');
            toggleBtn.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleBtn.classList.remove('fa-eye-slash');
            toggleBtn.classList.add('fa-eye');
        }
    }

    const loginForm = document.getElementById('loginForm');
    const loginToast = document.getElementById('loginToast');
    const toast = new bootstrap.Toast(loginToast);

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        try {
            const formData = new FormData(this);
            const response = await fetch('/shoppingcart/api/auth/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'bg-success');
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            } else {
                showToast(data.message, 'bg-danger');
            }
        } catch (error) {
            console.error('Login error:', error);
            showToast('Có lỗi xảy ra. Vui lòng thử lại sau.', 'bg-danger');
        }
    });

    function showToast(message, bgClass = 'bg-success') {
        const toastElement = document.getElementById('loginToast');
        toastElement.className = `toast align-items-center text-white border-0 ${bgClass}`;
        toastElement.querySelector('.toast-body').textContent = message;
        toast.show();
    }
</script>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?> 