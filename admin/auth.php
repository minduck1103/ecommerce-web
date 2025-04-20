<?php
require_once __DIR__ . '/../config/session.php';

/**
 * Function to check admin authentication and redirect to login if needed
 * Uses isAdmin() from session.php
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