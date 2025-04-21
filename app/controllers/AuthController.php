<?php
require_once 'BaseController.php';

class AuthController extends BaseController {
    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin');
                $this->redirect('auth/login');
                return;
            }

            $user = $this->model('User')->findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                $this->setFlashMessage('success', 'Đăng nhập thành công');
                $this->redirect('');
            } else {
                $this->setFlashMessage('error', 'Email hoặc mật khẩu không chính xác');
                $this->redirect('auth/login');
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $this->setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin');
                $this->redirect('auth/register');
                return;
            }

            if ($password !== $confirm_password) {
                $this->setFlashMessage('error', 'Mật khẩu xác nhận không khớp');
                $this->redirect('auth/register');
                return;
            }

            $userModel = $this->model('User');
            
            if ($userModel->findByEmail($email)) {
                $this->setFlashMessage('error', 'Email đã tồn tại trong hệ thống');
                $this->redirect('auth/register');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'user'
            ])) {
                $this->setFlashMessage('success', 'Đăng ký thành công! Vui lòng đăng nhập');
                $this->redirect('auth/login');
            } else {
                $this->setFlashMessage('error', 'Có lỗi xảy ra, vui lòng thử lại');
                $this->redirect('auth/register');
            }
        } else {
            $this->render('auth/register');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('');
    }
} 