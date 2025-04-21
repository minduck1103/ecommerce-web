<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

try {
    // Kiểm tra quyền admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        throw new Exception('Unauthorized access');
    }

    // Kiểm tra ID người dùng
    if (!isset($_GET['id'])) {
        throw new Exception('User ID is required');
    }

    $userId = intval($_GET['id']);

    // Kết nối database
    $conn = connectDB();

    // Lấy thông tin người dùng
    $query = "SELECT id, email, username, phone, role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        throw new Exception('User not found');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 