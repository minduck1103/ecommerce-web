<?php

class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return new class($stmt) {
                private $stmt;
                
                public function __construct($stmt) {
                    $this->stmt = $stmt;
                }
                
                public function single() {
                    return $this->stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                public function all() {
                    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            };
        } catch (PDOException $e) {
            // Log error
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }

    protected function insert($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $this->query($sql, $data);
        return $this->lastInsertId();
    }

    protected function update($data, $where) {
        $fields = array_map(function($field) {
            return $field . ' = :' . $field;
        }, array_keys($data));

        $whereFields = array_map(function($field) {
            return $field . ' = :where_' . $field;
        }, array_keys($where));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $this->table,
            implode(', ', $fields),
            implode(' AND ', $whereFields)
        );

        $params = $data;
        foreach ($where as $key => $value) {
            $params['where_' . $key] = $value;
        }

        return $this->query($sql, $params);
    }

    protected function delete($where) {
        $whereFields = array_map(function($field) {
            return $field . ' = :' . $field;
        }, array_keys($where));

        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $this->table,
            implode(' AND ', $whereFields)
        );

        return $this->query($sql, $where);
    }
} 