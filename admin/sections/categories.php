<?php
require_once '../../config/database.php';

// Ensure no output before headers
header('Content-Type: application/json');

try {
    // Test database connection
    if (!$conn) {
        throw new PDOException('Database connection failed');
    }

    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.id DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start(); // Start output buffering
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý danh mục</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Thêm danh mục
        </button>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Số sản phẩm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" onclick="editCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
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
        'message' => 'Có lỗi xảy ra khi tải dữ liệu danh mục: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi không xác định xảy ra: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 