-- Thêm danh mục mẫu
INSERT INTO categories (name, slug) VALUES
('Áo', 'ao'),
('Quần', 'quan'),
('Phụ kiện', 'phu-kien');

-- Thêm sản phẩm mẫu
INSERT INTO products (name, description, price, image, category_id, quantity, created_at) VALUES
('Áo thun basic', 'Áo thun cotton 100%', 199000, 'ao-thun-1.jpg', 1, 100, NOW()),
('Áo sơ mi trắng', 'Áo sơ mi công sở', 299000, 'ao-so-mi-1.jpg', 1, 50, NOW()),
('Quần jean nam', 'Quần jean cao cấp', 399000, 'quan-jean-1.jpg', 2, 30, NOW()),
('Quần tây', 'Quần tây công sở', 299000, 'quan-tay-1.jpg', 2, 40, NOW()),
('Thắt lưng da', 'Thắt lưng da bò', 199000, 'that-lung-1.jpg', 3, 20, NOW()),
('Ví da nam', 'Ví da thật', 299000, 'vi-da-1.jpg', 3, 25, NOW()),
('Áo khoác denim', 'Áo khoác jean thời trang', 499000, 'ao-khoac-1.jpg', 1, 15, NOW()),
('Quần short', 'Quần short thể thao', 199000, 'quan-short-1.jpg', 2, 60, NOW()); 