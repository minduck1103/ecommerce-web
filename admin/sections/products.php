<?php
// Cấu hình
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once '../../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Kiểm tra nếu đây là request AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    try {
        // Lấy danh sách sản phẩm với thông tin danh mục
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.image,
                p.original_price,
                p.price,
                p.stock,
                p.status,
                p.created_at,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Bắt đầu tạo HTML
        ob_start();
        ?>
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mt-4">Quản lý sản phẩm</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </button>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-table me-1"></i>
                        Danh sách sản phẩm
                    </div>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px">ID</th>
                                    <th style="width: 100px">Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th style="width: 120px">Giá gốc</th>
                                    <th style="width: 120px">Giá bán</th>
                                    <th style="width: 100px">Tồn kho</th>
                                    <th style="width: 100px">Trạng thái</th>
                                    <th style="width: 150px">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Không có sản phẩm nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['id']) ?></td>
                                            <td>
                                                <img src="../../uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                     class="img-thumbnail"
                                                     style="width: 80px; height: 80px; object-fit: cover;"
                                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                                     onerror="this.src='../../uploads/products/default.jpg'">
                                            </td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                            <td class="text-end"><?= number_format($product['original_price'], 0, ',', '.') ?>đ</td>
                                            <td class="text-end"><?= number_format($product['price'], 0, ',', '.') ?>đ</td>
                                            <td class="text-center"><?= htmlspecialchars($product['stock']) ?></td>
                                            <td class="text-center">
                                                <?php
                                                $statusClass = match($product['status']) {
                                                    'in-stock' => 'bg-success',
                                                    'out-of-stock' => 'bg-danger',
                                                    'sale' => 'bg-warning',
                                                    default => 'bg-secondary'
                                                };
                                                $statusText = match($product['status']) {
                                                    'in-stock' => 'Còn hàng',
                                                    'out-of-stock' => 'Hết hàng',
                                                    'sale' => 'Giảm giá',
                                                    default => 'Không xác định'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            onclick="editProduct(<?= $product['id'] ?>)"
                                                            title="Sửa sản phẩm">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteProduct(<?= $product['id'] ?>)"
                                                            title="Xóa sản phẩm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo DataTable cho bảng sản phẩm
            const productsTable = document.getElementById('productsTable');
            if (productsTable) {
                if ($.fn.DataTable.isDataTable(productsTable)) {
                    $(productsTable).DataTable().destroy();
                }
                new DataTable(productsTable, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }

            // Xử lý tìm kiếm sản phẩm
            const searchInput = document.getElementById('searchProduct');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchText = this.value.toLowerCase();
                    const rows = productsTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                    
                    for (let row of rows) {
                        const productName = row.cells[2].textContent.toLowerCase();
                        const categoryName = row.cells[3].textContent.toLowerCase();
                        
                        if (productName.includes(searchText) || categoryName.includes(searchText)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }
        });
        </script>
        <?php
        $html = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
} else {
    // Nếu không phải request AJAX, chuyển hướng về trang chủ
    header('Location: ../dashboard.php');
    exit;
} 