<?php

class ProductModel extends BaseModel {
    protected $table = 'products';

    public function getProductById($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        return $this->query($sql, [':id' => $id])->single();
    }

    public function getAllProducts($filters = [], $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "name LIKE :search";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                {$whereClause} 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
                
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->query($sql, $params)->all();
    }

    public function getTotalProducts($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "name LIKE :search";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT COUNT(*) as total FROM products {$whereClause}";
        $result = $this->query($sql, $params)->single();
        return $result['total'];
    }

    public function createProduct($data) {
        return $this->insert($data);
    }

    public function updateProduct($id, $data) {
        return $this->update($data, ['id' => $id]);
    }

    public function deleteProduct($id) {
        return $this->delete(['id' => $id]);
    }
} 