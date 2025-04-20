<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get email from POST data
$email = $_POST['email'] ?? '';

// Validate email
if (empty($email)) {
    echo json_encode(['exists' => false, 'message' => 'Email không được để trống']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false, 'message' => 'Email không hợp lệ']);
    exit;
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();
    
    echo json_encode([
        'exists' => $count > 0,
        'message' => $count > 0 ? 'Email hợp lệ' : 'Email không tồn tại'
    ]);
} catch (PDOException $e) {
    error_log("Database error in check-email.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Có lỗi xảy ra khi kiểm tra email',
        'message' => 'Vui lòng thử lại sau'
    ]);
} 