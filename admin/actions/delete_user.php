<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

try {
    // Kiểm tra quyền admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        throw new Exception('Unauthorized access');
    }

    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        throw new Exception('User ID is required');
    }

    $userId = intval($data['id']);

    // Không cho phép xóa chính mình
    if ($userId === $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }

    // Kết nối database
    $conn = connectDB();

    // Kiểm tra xem người dùng có phải là admin không
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $user['role'] == 1) {
        throw new Exception('Cannot delete admin account');
    }

    // Xóa người dùng
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete user');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 