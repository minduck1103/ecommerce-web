<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../admin/auth.php';

// Đảm bảo không có output nào trước header
ob_start();

header('Content-Type: application/json');

try {
    // Kiểm tra xác thực admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Không có quyền truy cập');
    }

    // Debug: In ra dữ liệu nhận được
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));

    // Kiểm tra dữ liệu đầu vào
    if (!isset($_POST['id'])) {
        throw new Exception('ID sản phẩm không được cung cấp');
    }

    $id = intval($_POST['id']);
    $name = $_POST['name'] ?? '';
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $status = intval($_POST['status']);
    $description = $_POST['description'] ?? '';

    // Validate dữ liệu
    if (empty($name)) {
        throw new Exception('Tên sản phẩm không được để trống');
    }
    if ($price <= 0) {
        throw new Exception('Giá sản phẩm phải lớn hơn 0');
    }
    if ($quantity < 0) {
        throw new Exception('Số lượng không được âm');
    }

    // Khởi tạo câu lệnh SQL và mảng tham số
    $sql = "UPDATE products SET name = ?, category_id = ?, price = ?, quantity = ?, status = ?, description = ?";
    $params = [$name, $category_id, $price, $quantity, $status, $description];

    // Xử lý upload ảnh nếu có
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $imageFileName = time() . '_' . basename($image['name']);
        $uploadPath = '../../uploads/products/' . $imageFileName;

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!is_dir('../../uploads/products')) {
            mkdir('../../uploads/products', 0777, true);
        }

        // Kiểm tra loại file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            throw new Exception('Chỉ chấp nhận file ảnh (JPG, PNG, GIF)');
        }

        // Kiểm tra kích thước file (max 5MB)
        if ($image['size'] > 5 * 1024 * 1024) {
            throw new Exception('Kích thước file không được vượt quá 5MB');
        }

        // Di chuyển file upload
        if (move_uploaded_file($image['tmp_name'], $uploadPath)) {
            // Xóa ảnh cũ nếu có
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $oldImage = $stmt->fetchColumn();
            
            if ($oldImage && file_exists('../../uploads/products/' . $oldImage)) {
                unlink('../../uploads/products/' . $oldImage);
            }

            // Thêm ảnh mới vào câu lệnh UPDATE
            $sql .= ", image = ?";
            $params[] = $imageFileName;
        } else {
            throw new Exception('Không thể upload file ảnh');
        }
    }

    // Hoàn thành câu lệnh SQL
    $sql .= " WHERE id = ?";
    $params[] = $id;

    // Thực hiện cập nhật
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        // Cập nhật trạng thái dựa trên số lượng
        $stmt = $conn->prepare("UPDATE products SET status = CASE WHEN quantity > 0 THEN 1 ELSE 0 END WHERE id = ?");
        $stmt->execute([$id]);

        // Lấy thông tin sản phẩm sau khi cập nhật
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        // Xóa bất kỳ output nào đã được tạo
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công',
            'product' => $updatedProduct
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Không thể cập nhật sản phẩm');
    }

} catch (Exception $e) {
    // Xóa bất kỳ output nào đã được tạo
    ob_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Đảm bảo không có output nào sau JSON
exit();
?> 