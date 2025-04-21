-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS shopping_cart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sử dụng database
USE shopping_cart;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category_id INT,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Áo thun', 'ao-thun', 'Các loại áo thun nam nữ'),
('Áo sweater', 'ao-sweater', 'Áo sweater phong cách'),
('Áo khoác', 'ao-khoac', 'Áo khoác thời trang'),
('Quần dài', 'quan-dai', 'Quần dài nam nữ'),
('Quần ngắn', 'quan-ngan', 'Quần ngắn thể thao');

-- Insert sample products
INSERT INTO products (name, category_id, description, price, original_price, stock, image, status) VALUES
('Áo thun basic trắng', 1, 'Áo thun cotton 100% màu trắng', 199000, 250000, 100, 'white-tshirt.jpg', 1),
('Sweater nỉ xám', 2, 'Áo sweater nỉ màu xám', 399000, 450000, 50, 'grey-sweater.jpg', 1),
('Áo khoác denim', 3, 'Áo khoác jean thời trang', 599000, 650000, 30, 'denim-jacket.jpg', 1),
('Quần jean nam', 4, 'Quần jean nam form regular', 499000, 550000, 40, 'mens-jeans.jpg', 1),
('Quần short thể thao', 5, 'Quần short thể thao thoáng mát', 299000, 350000, 60, 'sport-shorts.jpg', 1); 