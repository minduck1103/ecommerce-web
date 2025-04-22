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

// GET request - Lấy thông tin danh mục
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            echo json_encode([
                'success' => true,
                'category' => $category
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin danh mục'
        ]);
    }
}

// POST request - Thêm danh mục mới
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Kiểm tra xem slug đã tồn tại chưa
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmt->execute([$data['slug']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Slug đã tồn tại'
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Thêm danh mục thành công'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi thêm danh mục'
        ]);
    }
}

// PUT request - Cập nhật danh mục
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Kiểm tra xem slug đã tồn tại chưa (trừ danh mục hiện tại)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$data['slug'], $data['id']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Slug đã tồn tại'
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['id']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật danh mục'
        ]);
    }
}

// DELETE request - Xóa danh mục
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Kiểm tra xem danh mục có sản phẩm không
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$data['id']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa danh mục đang có sản phẩm'
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa danh mục thành công'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa danh mục'
        ]);
    }
} 