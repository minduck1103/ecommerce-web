<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .debug-section {
            margin-bottom: 20px;
        }
        .debug-header {
            background: #dc3545;
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .data-table {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="debug-header">
        <div class="container">
            <h1 class="h4 mb-0">Debug Information</h1>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <?php foreach ($debugInfo as $key => $value): ?>
                    <div class="debug-section">
                        <h3 class="h5 mb-3"><?php echo htmlspecialchars($key); ?></h3>
                        <?php if (is_array($value) || is_object($value)): ?>
                            <pre><code><?php print_r($value); ?></code></pre>
                        <?php else: ?>
                            <pre><code><?php echo htmlspecialchars($value); ?></code></pre>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="/shoppingcart" class="btn btn-primary">Về trang chủ</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 