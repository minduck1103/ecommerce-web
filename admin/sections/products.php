<?php
require_once '../../config/database.php';

// Ensure no output before headers
header('Content-Type: application/json');

try {
    // Test database connection
    if (!$conn) {
        throw new PDOException('Database connection failed');
    }

    // Update product status based on quantity
    $stmt = $conn->query("UPDATE products SET status = CASE WHEN quantity > 0 THEN 1 ELSE 0 END");

    $stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start(); // Start output buffering
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý sản phẩm</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </button>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <div style="width: 80px; height: 80px; overflow: hidden; border-radius: 8px;">
                                    <?php if (!empty($product['image']) && file_exists("../../uploads/products/" . $product['image'])): ?>
                                        <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../uploads/products/default.jpg" 
                                             alt="Default product image" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'Không có danh mục'); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                            <td><?php echo $product['quantity'] ?? 0; ?></td>
                            <td>
                                <?php if (isset($product['quantity']) && $product['quantity'] > 0): ?>
                                    <span class="badge bg-success">Còn hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Hết hàng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary me-1" onclick="editProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
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
        'message' => 'Có lỗi xảy ra khi tải dữ liệu sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi không xác định xảy ra: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 