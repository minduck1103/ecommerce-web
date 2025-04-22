<?php
session_start();
require_once '../includes/config.php';
require_once '../../app/config/database.php';

// Kiểm tra xác thực admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Khởi tạo kết nối database
try {
$database = new Database();
$conn = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Đặt header JSON
header('Content-Type: application/json');

// Xử lý DELETE request - Xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Lấy và validate dữ liệu từ body
        $rawData = file_get_contents('php://input');
        if (!$rawData) {
            throw new Exception('No data received');
            }

        $data = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data');
        }

        if (!isset($data['id'])) {
            throw new Exception('Missing user ID');
        }

        $userId = (int)$data['id'];
        if ($userId <= 0) {
            throw new Exception('Invalid user ID');
        }

        // Kiểm tra không cho phép xóa chính mình
        if ($userId === (int)$_SESSION['admin_id']) {
            throw new Exception('Cannot delete your own account');
            }

        // Kiểm tra user tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            throw new Exception('User not found');
        }

        // Thực hiện xóa
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt->execute([$userId])) {
            throw new Exception('Failed to delete user');
        }

        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully',
            'userId' => $userId
        ]);

    } catch (Exception $e) {
        error_log("Delete user error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => true
        ]);
            }
    exit;
}

// Xử lý GET request - Lấy thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $userId = (int)$_GET['id'];
        if ($userId <= 0) {
            throw new Exception('Invalid user ID');
            }

        $stmt = $conn->prepare("SELECT id, username, email, full_name, address, phone, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('User not found');
        }

        echo json_encode([
            'success' => true,
            'user' => $user
        ]);

    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => true
        ]);
    }
    exit;
}

// Method không được hỗ trợ
echo json_encode([
    'success' => false,
    'message' => 'Method not supported',
    'method' => $_SERVER['REQUEST_METHOD']
]);
exit;
?> 