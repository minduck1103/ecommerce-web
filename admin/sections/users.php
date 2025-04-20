<?php
require_once '../../config/database.php';

// Ensure no output before headers
header('Content-Type: application/json');

try {
    // Test database connection
    if (!$conn) {
        throw new PDOException('Database connection failed');
    }

    $stmt = $conn->prepare("SELECT * FROM users ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start(); // Start output buffering
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý người dùng</h2>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Họ tên</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php 
                                if (!empty($user['fullname'])) {
                                    echo htmlspecialchars($user['fullname']);
                                } else {
                                    echo '<span class="text-muted">Chưa cập nhật</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (!empty($user['phone'])) {
                                    echo htmlspecialchars($user['phone']);
                                } else {
                                    echo '<span class="text-muted">Chưa cập nhật</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">User</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary me-1" onclick="editUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean(); // Get the buffered content and clear the buffer

    echo json_encode([
        'success' => true,
        'html' => $html
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tải dữ liệu người dùng: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi không xác định xảy ra: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 