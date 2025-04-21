<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    // Kiểm tra dữ liệu gửi lên
    if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_POST['price'])) {
        throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
    }

    // Xử lý upload ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Chỉ chấp nhận file ảnh có định dạng: jpg, jpeg, png, gif');
        }

        $image = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $image;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            throw new Exception('Không thể upload ảnh');
        }
    }

    // Thêm sản phẩm vào database
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, quantity, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $_POST['name'],
        $_POST['category_id'],
        $_POST['price'],
        $_POST['quantity'] ?? 0,
        $_POST['description'] ?? '',
        $image
    ]);

    $productId = $conn->lastInsertId();

    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Thêm sản phẩm thành công',
        'product_id' => $productId
    ]);
    exit;

} catch (Exception $e) {
    // Xóa ảnh đã upload nếu có lỗi
    if (!empty($image) && file_exists($uploadDir . $image)) {
        unlink($uploadDir . $image);
    }

    // Trả về lỗi
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?> 