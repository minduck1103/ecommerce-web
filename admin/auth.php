<?php
require_once __DIR__ . '/../app/config/session.php';

/**
 * Function to check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['role']) && 
           $_SESSION['role'] === 'admin';
}

/**
 * Function to check admin authentication and redirect to login if needed
 */
function checkAdminAuth() {
    if (!isAdmin()) {
        // Nếu đang ở trong thư mục actions, điều hướng về login.php ở thư mục admin
        if (strpos($_SERVER['PHP_SELF'], '/admin/actions/') !== false) {
            header('Location: ../login.php');
        } else {
            header('Location: login.php');
        }
        exit();
    }
} 