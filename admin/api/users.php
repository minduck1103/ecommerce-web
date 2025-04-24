<?php
session_start();
require_once '../includes/config.php';
require_once '../../app/config/database.php';

// Set up logging
function debug_log($message, $data = null) {
    $log_file = __DIR__ . '/debug.log';
    
    // Tạo file nếu chưa tồn tại
    if (!file_exists($log_file)) {
        touch($log_file);
        chmod($log_file, 0666);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}";
    if ($data !== null) {
        $log_message .= "\n" . print_r($data, true);
    }
    $log_message .= "\n-------------------------------------------\n";
    
    // Thêm error reporting
    error_log("Writing to log file: " . $log_file);
    $result = file_put_contents($log_file, $log_message, FILE_APPEND);
    if ($result === false) {
        error_log("Failed to write to log file. Error: " . error_get_last()['message']);
    }
}

// Test log function
debug_log('API initialized', ['time' => date('Y-m-d H:i:s')]);

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

// Đặt header JSON
header('Content-Type: application/json');

// DELETE request - Xóa user
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validate input
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID người dùng không hợp lệ'
            ]);
            exit;
        }

        $userId = (int)$data['id'];

        // Kiểm tra xem có phải đang tự xóa chính mình không
        if ($userId == $_SESSION['admin_id']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa tài khoản đang đăng nhập'
            ]);
            exit;
        }

        // Kiểm tra user tồn tại và role
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ]);
            exit;
        }

        if ($user['role'] === 'admin') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa tài khoản admin khác'
            ]);
            exit;
        }

        // Thực hiện xóa user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $success = $stmt->execute([$userId]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa người dùng thành công'
            ]);
        } else {
            throw new Exception('Không thể xóa người dùng');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa người dùng: ' . $e->getMessage()
        ]);
    }
    exit;
}

// GET request - Lấy thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $userId = (int)$_GET['id'];
        if ($userId <= 0) {
            throw new Exception('ID người dùng không hợp lệ');
        }

        $stmt = $conn->prepare("SELECT id, email, full_name, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('Không tìm thấy người dùng');
        }

        echo json_encode([
            'success' => true,
            'user' => $user
        ]);

    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Method không được hỗ trợ
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not supported'
]);
exit;
?> 