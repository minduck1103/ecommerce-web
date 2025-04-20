<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopCart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">ShopCart Admin</a>
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Admin Login
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Welcome to ShopCart Admin</h1>
            <p class="lead">Powerful tools to manage your e-commerce platform</p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-box feature-icon"></i>
                            <h3>Product Management</h3>
                            <p>Easily manage your product catalog, update prices, and control inventory.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shopping-cart feature-icon"></i>
                            <h3>Order Processing</h3>
                            <p>Track and manage orders, update order status, and handle customer requests.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users feature-icon"></i>
                            <h3>User Management</h3>
                            <p>Manage user accounts, roles, and permissions with ease.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Ready to get started?</h2>
            <a href="login.php" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 ShopCart. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 