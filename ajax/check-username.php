<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = $_POST['username'] ?? '';

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

// Check if username exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

echo json_encode([
    'success' => true,
    'exists' => (bool)$user
]); 