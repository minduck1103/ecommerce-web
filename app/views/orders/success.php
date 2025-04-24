<?php
session_start();

// Kiểm tra xem có order_id trong session không
if (!isset($_SESSION['last_order_id'])) {
    header('Location: /shoppingcart/');
    exit;
}

$order_id = $_SESSION['last_order_id'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Fashion Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(146, 155, 161);
            --success-color: #2ecc71;
            --text-color: #2d3748;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success-color);
            margin-bottom: 1.5rem;
            animation: scaleIn 0.5s ease;
        }

        .success-title {
            font-size: 2rem;
            color: var(--text-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .btn-action {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), rgb(169, 177, 183));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, rgb(83, 158, 163), var(--primary-color));
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            border: 2px solid var(--primary-color);
            color: var(--text-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="success-title">Đặt hàng thành công!</h1>
        <p class="success-message">Cảm ơn bạn đã mua sắm tại Fashion Shop. Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p>
        <div class="buttons mt-4">
            <a href="/shoppingcart/products" class="btn btn-outline-secondary btn-action">
                <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
            </a>
            <a href="/shoppingcart/account/orders" class="btn btn-primary btn-action">
                <i class="fas fa-box me-2"></i>Xem đơn hàng
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 