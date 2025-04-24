<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure no output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set JSON header
header('Content-Type: application/json');

// Debug information
$debug = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'query_string' => $_SERVER['QUERY_STRING'],
    'get_params' => $_GET,
    'post_params' => $_POST,
    'files' => $_FILES
];

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add session info to debug
$debug['session_data'] = $_SESSION;

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access',
        'debug' => $debug
    ]);
    exit;
}

// Include database
require_once __DIR__ . '/../../app/config/database.php';

// Connect to database
try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => $debug
    ]);
    exit;
}

// Get action from either GET or POST
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$debug['action'] = $action;

// Handle actions
try {
    switch ($action) {
        case 'get':
            // Get product by ID
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }

            $stmt = $conn->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Không tìm thấy sản phẩm');
            }

            echo json_encode([
                'success' => true, 
                'product' => $product,
                'debug' => $debug
            ]);
            break;

        case 'create':
            // Create new product
            if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_POST['price'])) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
            }

            // Generate slug from name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
            
            // Ensure unique slug
            $baseSlug = $slug;
            $counter = 1;
            do {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
                $stmt->execute([$slug]);
                $exists = $stmt->fetchColumn();
                if ($exists) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
            } while ($exists);

            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception('Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)');
                }

                $image = uniqid() . '.' . $ext;
                $upload_path = __DIR__ . '/../../public/uploads/products/';
                
                // Create uploads directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $image)) {
                    throw new Exception('Không thể tải lên hình ảnh');
                }
            }

            // Set default status based on quantity
            $quantity = $_POST['quantity'] ?? 0;
            $status = $quantity > 0 ? 'in-stock' : 'out-of-stock';

            $stmt = $conn->prepare("
                INSERT INTO products (
                    name, slug, category_id, price, original_price, 
                    quantity, description, image, status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            $stmt->execute([
                $_POST['name'],
                $slug,
                $_POST['category_id'],
                $_POST['price'],
                $_POST['original_price'] ?? $_POST['price'],
                $quantity,
                $_POST['description'] ?? '',
                $image,
                $status
            ]);

            // Get the ID of the newly inserted product
            $newProductId = $conn->lastInsertId();

            echo json_encode([
                'success' => true, 
                'message' => 'Thêm sản phẩm thành công',
                'product_id' => $newProductId
            ]);
            break;

        case 'update':
            // Update product
            if (empty($_POST['id'])) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }

            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                throw new Exception('Không tìm thấy sản phẩm');
            }

            $image = $current['image'];
            if (!empty($_FILES['image']['name'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception('Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)');
                }

                $image = uniqid() . '.' . $ext;
                $upload_path = __DIR__ . '/../../public/uploads/products/';
                
                // Create uploads directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $image)) {
                    throw new Exception('Không thể tải lên hình ảnh');
                }

                // Delete old image
                if ($current['image'] && file_exists($upload_path . $current['image'])) {
                    unlink($upload_path . $current['image']);
                }
            }

            // Update slug if name changed
            $slug = null;
            if (!empty($_POST['name'])) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
                
                // Ensure unique slug
                $baseSlug = $slug;
                $counter = 1;
                do {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
                    $stmt->execute([$slug, $_POST['id']]);
                    $exists = $stmt->fetchColumn();
                    if ($exists) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                } while ($exists);
            }

            // Set status based on quantity
            $quantity = $_POST['quantity'] ?? 0;
            $status = $quantity > 0 ? 'in-stock' : 'out-of-stock';

            $stmt = $conn->prepare("
                UPDATE products SET 
                    name = ?, 
                    slug = ?,
                    category_id = ?, 
                    price = ?, 
                    original_price = ?,
                    quantity = ?, 
                    description = ?, 
                    image = ?,
                    status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['name'],
                $slug,
                $_POST['category_id'],
                $_POST['price'],
                $_POST['original_price'] ?? $_POST['price'],
                $quantity,
                $_POST['description'] ?? '',
                $image,
                $status,
                $_POST['id']
            ]);

            // Get updated product data with category name
            $stmt = $conn->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$_POST['id']]);
            $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true, 
                'message' => 'Cập nhật sản phẩm thành công',
                'product' => $updatedProduct
            ]);
            break;

        case 'delete':
            // Delete product
            if (empty($_POST['id'])) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }

            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);

            if ($product && $product['image']) {
                $image_path = __DIR__ . '/../../public/uploads/products/' . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
            break;

        default:
            throw new Exception('Hành động không hợp lệ');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => $debug,
        'error_trace' => $e->getTraceAsString()
    ]);
} 