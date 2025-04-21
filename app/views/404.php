<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <h1>404</h1>
            <h2>Page Not Found</h2>
            <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            <a href="/shoppingcart" class="home-button">Go to Homepage</a>
        </div>
    </div>

    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .error-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background-color: #f8f9fa;
    }

    .error-content {
        text-align: center;
        max-width: 600px;
    }

    .error-content h1 {
        font-size: 6rem;
        font-weight: 700;
        color: #4CAF50;
        margin: 0;
        line-height: 1;
    }

    .error-content h2 {
        font-size: 2rem;
        color: #333;
        margin: 1rem 0;
    }

    .error-content p {
        color: #666;
        margin-bottom: 2rem;
    }

    .home-button {
        display: inline-block;
        padding: 1rem 2rem;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .home-button:hover {
        background-color: #45a049;
        color: white;
        text-decoration: none;
    }

    @media (max-width: 480px) {
        .error-content h1 {
            font-size: 4rem;
        }
        
        .error-content h2 {
            font-size: 1.5rem;
        }
    }
    </style>
</body>
</html> 