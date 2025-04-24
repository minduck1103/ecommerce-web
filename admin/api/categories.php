<?php
session_start();
require_once '../config.php';
require_once '../../app/config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Khởi tạo kết nối database
$database = new Database();
$conn = $database->getConnection();

header('Content-Type: application/json');

// Hàm tạo slug từ tên
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// GET request - Lấy thông tin danh mục
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'category' => $category
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin danh mục'
        ]);
    }
}

// POST request - Thêm/sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Tạo slug từ tên
        $slug = createSlug($data['name']);
        
        if (isset($data['id'])) {
            // Cập nhật danh mục
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$data['name'], $slug, $data['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công'
            ]);
        } else {
            // Thêm danh mục mới
            $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$data['name'], $slug]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Thêm danh mục thành công',
                'id' => $conn->lastInsertId()
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lưu danh mục'
        ]);
    }
}

// DELETE request - Xóa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Đọc dữ liệu từ request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID danh mục'
        ]);
        exit;
    }

    try {
        // Kiểm tra xem danh mục có tồn tại không
        $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$data['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ]);
            exit;
        }

        // Thực hiện xóa danh mục
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa danh mục thành công'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa danh mục: ' . $e->getMessage()
        ]);
    }
} 