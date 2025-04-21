<?php

class OrderModel extends BaseModel {
    public function createOrder($data) {
        $sql = "INSERT INTO orders (user_id, full_name, email, phone, address, note, 
                total_amount, status, created_at) 
                VALUES (:user_id, :full_name, :email, :phone, :address, :note, 
                :total_amount, :status, NOW())";
        
        $params = [
            ':user_id' => $data['user_id'],
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':address' => $data['address'],
            ':note' => $data['note'],
            ':total_amount' => $data['total_amount'],
            ':status' => $data['status']
        ];
        
        $this->query($sql, $params);
        return $this->lastInsertId();
    }

    public function addOrderItem($data) {
        $sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                VALUES (:order_id, :product_id, :product_name, :quantity, :price)";
        
        $params = [
            ':order_id' => $data['order_id'],
            ':product_id' => $data['product_id'],
            ':product_name' => $data['product_name'],
            ':quantity' => $data['quantity'],
            ':price' => $data['price']
        ];
        
        return $this->query($sql, $params);
    }

    public function getOrderById($orderId) {
        $sql = "SELECT o.*, u.email as user_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = :order_id";
        
        return $this->query($sql, [':order_id' => $orderId])->single();
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        
        return $this->query($sql, [':order_id' => $orderId])->all();
    }

    public function getUserOrders($userId) {
        $sql = "SELECT * FROM orders 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        
        return $this->query($sql, [':user_id' => $userId])->all();
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollback() {
        return $this->db->rollBack();
    }
} 