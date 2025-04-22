<?php
require_once 'Model.php';

class Product extends Model {
    public function getAllProducts() {
        $query = "SELECT p.id, p.name, p.price, p.quantity, p.description, p.image, p.status, p.created_at, 
                         c.name as category_name, c.id as category_id
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 'in-stock'
                 ORDER BY p.created_at DESC";
        
        // Debug query
        error_log("Products query: " . $query);
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug results
        error_log("Products found: " . count($products));
        if (count($products) > 0) {
            error_log("Sample product data: " . print_r($products[0], true));
        }
        
        return $products;
    }

    public function getProductById($id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = ? AND p.status = 'in-stock'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProduct($data) {
        $query = "INSERT INTO products (name, category_id, price, quantity, description, image) 
                 VALUES (:name, :category_id, :price, :quantity, :description, :image)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':category_id' => $data['category_id'],
            ':price' => $data['price'],
            ':quantity' => $data['quantity'],
            ':description' => $data['description'],
            ':image' => $data['image']
        ]);
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE products 
                 SET name = :name, 
                     category_id = :category_id, 
                     price = :price, 
                     quantity = :quantity, 
                     description = :description";
        
        if (isset($data['image'])) {
            $query .= ", image = :image";
        }
        
        $query .= " WHERE id = :id";
        
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':category_id' => $data['category_id'],
            ':price' => $data['price'],
            ':quantity' => $data['quantity'],
            ':description' => $data['description']
        ];

        if (isset($data['image'])) {
            $params[':image'] = $data['image'];
        }

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteProduct($id) {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getLatestProducts($limit = 8) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 'in-stock' 
                 ORDER BY p.created_at DESC 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBestSellers($limit = 8) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 'in-stock' 
                 ORDER BY p.quantity DESC 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedProducts($categoryId, $excludeId, $limit = 4) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = :category_id 
                 AND p.id != :exclude_id 
                 ORDER BY RAND() 
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->bindParam(':exclude_id', $excludeId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterProducts($categories = [], $priceFrom = null, $priceTo = null, $sort = 'newest') {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE 1=1";
        $params = [];
        
        if (!empty($categories)) {
            $placeholders = str_repeat('?,', count($categories) - 1) . '?';
            $query .= " AND c.slug IN ($placeholders)";
            $params = array_merge($params, $categories);
        }
        
        if ($priceFrom !== null) {
            $query .= " AND p.price >= ?";
            $params[] = $priceFrom;
        }
        
        if ($priceTo !== null) {
            $query .= " AND p.price <= ?";
            $params[] = $priceTo;
        }
        
        switch ($sort) {
            case 'price-asc':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price-desc':
                $query .= " ORDER BY p.price DESC";
                break;
            default:
                $query .= " ORDER BY p.created_at DESC";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 