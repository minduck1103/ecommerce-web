<?php
require_once __DIR__ . '/../config/session.php';

// Function to check admin authentication and redirect to admin login if needed
function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}
?> 