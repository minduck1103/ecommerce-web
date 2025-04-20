-- Create database
CREATE DATABASE IF NOT EXISTS shopping_cart;
USE shopping_cart;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2),
    stock INT NOT NULL DEFAULT 0,
    status ENUM('in-stock', 'out-of-stock', 'sale') DEFAULT 'in-stock',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product images table (for multiple images)
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    note TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert sample data for categories
INSERT INTO categories (name, slug, description) VALUES
('Áo thun', 'ao-thun', 'Các loại áo thun thời trang'),
('Áo sweater', 'ao-sweater', 'Áo sweater ấm áp'),
('Áo khoác', 'ao-khoac', 'Áo khoác thời trang'),
('Quần dài', 'quan-dai', 'Quần dài thời trang'),
('Quần ngắn', 'quan-ngan', 'Quần ngắn thời trang');

-- Insert sample data for products
INSERT INTO products (category_id, name, slug, description, price, original_price, stock, status, image) VALUES
(1, 'Áo thun basic', 'ao-thun-basic', 'Áo thun basic với chất liệu cotton 100%, form regular fit thoải mái', 199000, 249000, 100, 'sale', 'https://via.placeholder.com/300x300'),
(1, 'Áo thun cổ tròn', 'ao-thun-co-tron', 'Áo thun cổ tròn với chất liệu cotton 100%, form regular fit thoải mái', 179000, NULL, 50, 'in-stock', 'https://via.placeholder.com/300x300'),
(2, 'Áo sweater', 'ao-sweater', 'Áo sweater ấm áp, phù hợp mùa đông', 299000, NULL, 30, 'in-stock', 'https://via.placeholder.com/300x300'),
(3, 'Áo khoác denim', 'ao-khoac-denim', 'Áo khoác denim thời trang', 399000, NULL, 20, 'in-stock', 'https://via.placeholder.com/300x300'),
(4, 'Quần jean', 'quan-jean', 'Quần jean thời trang', 259000, NULL, 40, 'in-stock', 'https://via.placeholder.com/300x300');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$8KzQ8IzAF1QDMV0oNvqH8.9X9X9X9X9X9X9X9X9X9X9X9X9X9X9X', 'admin@example.com', 'Administrator', 'admin');

-- Insert sample regular user (password: user123)
INSERT INTO users (username, password, email, full_name, role) VALUES
('user', '$2y$10$8KzQ8IzAF1QDMV0oNvqH8.9X9X9X9X9X9X9X9X9X9X9X9X9X9X9X', 'user@example.com', 'Regular User', 'user'); 