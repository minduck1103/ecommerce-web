<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Initialize database connection
        $database = new Database();
        $conn = $database->getConnection();

        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to dashboard
            header("Location: ../dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = 'Email hoặc mật khẩu không đúng';
            header("Location: ../login.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['login_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        header("Location: ../login.php");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
} 