<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$formData = [
    'email' => '',
];

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
            $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$formData['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($formData['password'], $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to admin dashboard
                header('Location: dashboard.php');
                exit();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ShopCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            padding: 20px;
            text-align: center;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-primary {
            padding: 12px;
            font-weight: 500;
        }
        .back-to-home {
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back-to-home:hover {
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Admin Login</h3>
            </div>
            <div class="card-body">
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
                               id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center">
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left me-2"></i>Back to Admin Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        loginForm.addEventListener('submit', function(e) {
            if (!emailInput.value.trim() || !passwordInput.value.trim()) {
                e.preventDefault();
                alert('Please enter both email and password');
            }
        });
    </script>
</body>
</html> 