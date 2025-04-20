<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';
$success = '';

// Check for success message from registration
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email hoặc mật khẩu không chính xác';
            }
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand mx-auto" href="index.php">ShopCart</a>
        </div>
    </nav>

    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Đăng nhập</h3>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php" id="loginForm">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <label for="email">Email</label>
                            <div id="emailFeedback" class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                            <label for="password">Mật khẩu</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            <a href="register.php" class="btn btn-outline-secondary">Đăng ký tài khoản mới</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('emailFeedback');
        let emailTimeout;

        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            const email = this.value.trim();
            
            if (email === '') {
                emailFeedback.textContent = 'Vui lòng nhập email';
                emailFeedback.className = 'invalid-feedback';
                this.classList.add('is-invalid');
                return;
            }

            if (!isValidEmail(email)) {
                emailFeedback.textContent = 'Email không hợp lệ';
                emailFeedback.className = 'invalid-feedback';
                this.classList.add('is-invalid');
                return;
            }

            emailTimeout = setTimeout(() => {
                fetch('ajax/check-email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailFeedback.textContent = 'Email hợp lệ';
                        emailFeedback.className = 'valid-feedback';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        emailFeedback.textContent = 'Email không tồn tại';
                        emailFeedback.className = 'invalid-feedback';
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    emailFeedback.textContent = 'Có lỗi xảy ra';
                    emailFeedback.className = 'invalid-feedback';
                    this.classList.add('is-invalid');
                });
            }, 300);
        });

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
    </script>
</body>
</html> 