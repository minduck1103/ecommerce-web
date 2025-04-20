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

// Check for success message from registration
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    
    // Validate input
    $errors = [];
    
    // Email validation
    if (empty($formData['email'])) {
        $errors['email'] = 'Vui lòng nhập email';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ';
    }
    
    // Password validation
    if (empty($formData['password'])) {
        $errors['password'] = 'Vui lòng nhập mật khẩu';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        try {
            // Get user with role
            $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$formData['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($formData['password'], $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Check role and redirect
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                    exit();
                } else {
                    header('Location: index.php');
                    exit();
                }
            } else {
                $error = 'Email hoặc mật khẩu không đúng';
            }
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
        }
    } else {
        $error = 'Vui lòng kiểm tra lại thông tin';
    }
}

// Handle forgot password form submission
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $conn->prepare("
                    INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user['id'], $token, $expires]);
                
                // Send reset email (implement your email sending logic here)
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
                // mail($email, "Reset Password", "Click here to reset your password: " . $resetLink);
                
                $success = 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn';
            } else {
                $error = 'Email không tồn tại trong hệ thống';
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
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            <a href="register.php" class="btn btn-outline-secondary">Chưa có tài khoản? Đăng ký</a>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Quên mật khẩu?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quên mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="login.php" id="forgotPasswordForm">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="forgot_email" name="email" placeholder="Email" required>
                            <label for="forgot_email">Email</label>
                        </div>
                        <input type="hidden" name="forgot_password" value="1">
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time validation
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        // Form submission validation
        loginForm.addEventListener('submit', function(e) {
            if (!emailInput.value.trim() || !passwordInput.value.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ thông tin bắt buộc');
            }
        });
        
        // Forgot password form validation
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const forgotEmailInput = document.getElementById('forgot_email');
        
        forgotPasswordForm.addEventListener('submit', function(e) {
            if (!forgotEmailInput.value.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập email của bạn');
            }
        });
    </script>
</body>
</html> 