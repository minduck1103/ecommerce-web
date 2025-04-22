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
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --text-color: #2d3748;
            --text-muted: #718096;
            --background-color: #f8f9fa;
            --border-color: #edf2f7;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .success-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 0 1rem;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 2rem;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 40px;
            color: white;
        }

        .success-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .success-message {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .order-number {
            background: var(--background-color);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px dashed var(--border-color);
        }

        .order-number span {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #2980b9);
            border: none;
            color: white;
        }

        .btn-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .additional-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 12px;
            text-align: left;
        }

        .additional-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-muted);
        }

        .info-list li i {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 2rem auto;
            }

            .success-card {
                padding: 1.5rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="success-title">Đặt hàng thành công!</h1>
            <p class="success-message">
                Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.
            </p>
            <div class="order-number">
                Mã đơn hàng: <span>#<?php echo $order_id; ?></span>
            </div>
            <div class="additional-info">
                <h3>Thông tin quan trọng:</h3>
                <ul class="info-list">
                    <li>
                        <i class="fas fa-envelope"></i>
                        Bạn sẽ nhận được email xác nhận đơn hàng
                    </li>
                    <li>
                        <i class="fas fa-truck"></i>
                        Thời gian giao hàng dự kiến: 2-3 ngày
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        Chúng tôi sẽ liên hệ nếu cần thêm thông tin
                    </li>
                </ul>
            </div>
            <div class="btn-group">
                <a href="/shoppingcart/account/orders" class="btn btn-primary">
                    <i class="fas fa-list-ul"></i>
                    Xem đơn hàng
                </a>
                <a href="/shoppingcart/" class="btn btn-outline">
                    <i class="fas fa-home"></i>
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 