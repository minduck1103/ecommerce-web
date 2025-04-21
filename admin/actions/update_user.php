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

    if (!isset($data['id']) || !isset($data['email'])) {
        throw new Exception('Missing required fields');
    }

    // Kết nối database
    $conn = connectDB();

    // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
    $query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $data['email'], $data['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($result)) {
        throw new Exception('Email already exists');
    }

    // Cập nhật thông tin người dùng
    if (!empty($data['password'])) {
        // Nếu có cập nhật mật khẩu
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET email = ?, username = ?, phone = ?, role = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", 
            $data['email'],
            $data['username'],
            $data['phone'],
            $data['role'],
            $password_hash,
            $data['id']
        );
    } else {
        // Nếu không cập nhật mật khẩu
        $query = "UPDATE users SET email = ?, username = ?, phone = ?, role = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", 
            $data['email'],
            $data['username'],
            $data['phone'],
            $data['role'],
            $data['id']
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 