<?php
require_once 'BaseController.php';

class AjaxController extends BaseController {
    public function checkEmail() {
        // Code from ajax/check-email.php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $this->json([
                'exists' => $stmt->fetch() ? true : false
            ]);
        }
    }

    public function checkUsername() {
        // Code from ajax/check-username.php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            
            $query = "SELECT id FROM users WHERE username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $this->json([
                'exists' => $stmt->fetch() ? true : false
            ]);
        }
    }
} 