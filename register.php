<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$formData = [
    'email' => '',
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];
    
    // Validate input
    $errors = [];
    
    // Email validation
    if (empty($formData['email'])) {
        $errors['email'] = 'Vui lòng nhập email';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$formData['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }
    
    // Password validation
    if (empty($formData['password'])) {
        $errors['password'] = 'Vui lòng nhập mật khẩu';
    } elseif (strlen($formData['password']) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp';
    }
    
    // If no errors, create user
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO users (email, password, role) 
                VALUES (?, ?, 'user')
            ");
            
            $stmt->execute([
                $formData['email'],
                password_hash($formData['password'], PASSWORD_DEFAULT)
            ]);
            
            $_SESSION['success_message'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
            header('Location: login.php');
            exit;
            
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
        }
    } else {
        $error = 'Vui lòng kiểm tra lại thông tin';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 50px auto;
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
        <div class="register-container">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Đăng ký tài khoản</h3>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php" id="registerForm">
                        <div class="form-floating">
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" name="email" placeholder="Email" 
                                   value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                            <label for="email">Email</label>
                            <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   id="password" name="password" placeholder="Mật khẩu" required>
                            <label for="password">Mật khẩu</label>
                            <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                            <label for="confirm_password">Xác nhận mật khẩu</label>
                            <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Đăng ký</button>
                            <a href="login.php" class="btn btn-outline-secondary">Đã có tài khoản? Đăng nhập</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time validation
        const registerForm = document.getElementById('registerForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Check email existence
        emailInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                fetch('ajax/check-email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(this.value)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        this.setCustomValidity('Email đã tồn tại');
                    } else {
                        this.setCustomValidity('');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
        
        // Password confirmation validation
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && this.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
        
        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            if (!emailInput.value.trim() || 
                !passwordInput.value.trim() || !confirmPasswordInput.value.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ thông tin bắt buộc');
            }
        });
    </script>
</body>
</html> 