<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Lấy dữ liệu người dùng
$query = "SELECT id, email, username, phone, role, created_at FROM users ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

$users = [];
while ($row = $result->fetch()) {
    $users[] = [
        'id' => (int)$row['id'],
        'email' => $row['email'],
        'username' => $row['username'] ?? '',
        'phone' => $row['phone'] ?? '',
        'role' => (int)$row['role'],
        'created_at' => $row['created_at']
    ];
}

// Cấu trúc template
$template = [
    'title' => 'Quản lý người dùng',
    'table_headers' => ['ID', 'Email', 'Tên người dùng', 'Số điện thoại', 'Vai trò', 'Ngày tạo', 'Thao tác'],
    'modal' => [
        'title' => 'Chỉnh sửa thông tin người dùng',
        'fields' => [
            [
                'type' => 'email',
                'id' => 'editUserEmail',
                'name' => 'email',
                'label' => 'Email',
                'required' => true
            ],
            [
                'type' => 'text',
                'id' => 'editUserUsername',
                'name' => 'username',
                'label' => 'Tên người dùng'
            ],
            [
                'type' => 'tel',
                'id' => 'editUserPhone',
                'name' => 'phone',
                'label' => 'Số điện thoại'
            ],
            [
                'type' => 'select',
                'id' => 'editUserRole',
                'name' => 'role',
                'label' => 'Vai trò',
                'options' => [
                    ['value' => '0', 'text' => 'User'],
                    ['value' => '1', 'text' => 'Admin']
                ]
            ],
            [
                'type' => 'password',
                'id' => 'editUserPassword',
                'name' => 'password',
                'label' => 'Mật khẩu mới (để trống nếu không đổi)'
            ]
        ]
    ]
];

// Trả về JSON response
exit(json_encode([
    'success' => true,
    'data' => [
        'users' => $users,
        'template' => $template
    ]
]));
?> 