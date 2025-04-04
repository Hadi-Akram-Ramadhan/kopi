CREATE DATABASE IF NOT EXISTS cafe_db;
USE cafe_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('kasir', 'manajer', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category ENUM('makanan', 'minuman') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('tersedia', 'tidak_tersedia') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_number INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'selesai') DEFAULT 'pending',
    cashier_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    menu_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$OkupL.oS16TYw/NbGGZl7OqFW4H.RV/ZhvhJyWFafJB3rNOLvwQES', 'admin');

-- Insert dummy users (password: user123)
INSERT INTO users (username, password, role) VALUES 
('kasir1', '$2y$10$5iKNE4E/uC6iEzyPKpy6B.y1DmwNQd4zUaGthz97KcYEM....RWSO', 'kasir'),
('kasir2', '$2y$10$ZyC3MGVrsnVI3AiohvpUZeqb5g5n/DEml1ZJ9oUCkijhpyjCwaJBq', 'kasir'),
('manajer1', '$2y$10$vL7wbXV4uYdOcBaMIQWUseyTCM60ZtC5vcSlUsYzKd4IikquR7BXO', 'manajer'),
('manajer2', '$2y$10$2tQwNlEM21D.C5AwKSg8jOrw8Z0vkbVkVLx6Ec3h8ZRaz2wwrIBWS', 'manajer');

-- Insert dummy menu
INSERT INTO menu (name, category, price) VALUES 
-- Makanan
('Nasi Goreng Spesial', 'makanan', 25000),
('Mie Goreng', 'makanan', 20000),
('Ayam Goreng', 'makanan', 18000),
('Sate Ayam', 'makanan', 15000),
('Gado-gado', 'makanan', 12000),
('Soto Ayam', 'makanan', 15000),
('Rendang', 'makanan', 30000),
('Nasi Uduk', 'makanan', 12000),
('Bakso', 'makanan', 15000),
('Soto Betawi', 'makanan', 20000),

-- Minuman
('Kopi Hitam', 'minuman', 8000),
('Kopi Susu', 'minuman', 10000),
('Teh Tarik', 'minuman', 8000),
('Es Teh', 'minuman', 5000),
('Es Jeruk', 'minuman', 6000),
('Es Campur', 'minuman', 15000),
('Jus Alpukat', 'minuman', 12000),
('Jus Mangga', 'minuman', 12000),
('Jus Melon', 'minuman', 12000),
('Jus Sirsak', 'minuman', 12000);

-- Insert dummy orders
INSERT INTO orders (table_number, total_amount, status, cashier_id, created_at) VALUES 
(1, 75000, 'selesai', 2, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 120000, 'selesai', 2, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 85000, 'selesai', 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 95000, 'selesai', 3, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 65000, 'selesai', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 110000, 'selesai', 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 80000, 'selesai', 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 90000, 'pending', 3, NOW());

-- Insert dummy order items
INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES 
-- Order 1
(1, 1, 2, 25000), -- Nasi Goreng Spesial
(1, 11, 3, 8000), -- Kopi Hitam
(1, 12, 1, 10000), -- Kopi Susu

-- Order 2
(2, 3, 2, 18000), -- Ayam Goreng
(2, 4, 3, 15000), -- Sate Ayam
(2, 13, 2, 8000), -- Teh Tarik
(2, 14, 2, 5000), -- Es Teh

-- Order 3
(3, 2, 2, 20000), -- Mie Goreng
(3, 5, 1, 12000), -- Gado-gado
(3, 11, 2, 8000), -- Kopi Hitam
(3, 12, 2, 10000), -- Kopi Susu
(3, 15, 1, 6000), -- Es Jeruk

-- Order 4
(4, 6, 2, 15000), -- Soto Ayam
(4, 7, 1, 30000), -- Rendang
(4, 13, 3, 8000), -- Teh Tarik
(4, 14, 2, 5000), -- Es Teh

-- Order 5
(5, 8, 2, 12000), -- Nasi Uduk
(5, 9, 1, 15000), -- Bakso
(5, 11, 2, 8000), -- Kopi Hitam
(5, 12, 1, 10000), -- Kopi Susu

-- Order 6
(6, 10, 2, 20000), -- Soto Betawi
(6, 16, 2, 15000), -- Es Campur
(6, 17, 2, 12000), -- Jus Alpukat
(6, 18, 2, 12000), -- Jus Mangga
(6, 19, 2, 12000), -- Jus Melon
(6, 20, 2, 12000), -- Jus Sirsak

-- Order 7
(7, 1, 1, 25000), -- Nasi Goreng Spesial
(7, 2, 1, 20000), -- Mie Goreng
(7, 11, 2, 8000), -- Kopi Hitam
(7, 12, 2, 10000), -- Kopi Susu
(7, 13, 1, 8000), -- Teh Tarik

-- Order 8 (pending)
(8, 3, 2, 18000), -- Ayam Goreng
(8, 4, 2, 15000), -- Sate Ayam
(8, 11, 3, 8000), -- Kopi Hitam
(8, 12, 2, 10000), -- Kopi Susu
(8, 13, 2, 8000); -- Teh Tarik

-- Insert dummy activity logs
INSERT INTO activity_logs (user_id, activity) VALUES 
(1, 'Login ke sistem'),
(2, 'Login ke sistem'),
(2, 'Membuat transaksi baru #1'),
(2, 'Menyelesaikan transaksi #1'),
(2, 'Membuat transaksi baru #2'),
(2, 'Menyelesaikan transaksi #2'),
(3, 'Login ke sistem'),
(3, 'Membuat transaksi baru #3'),
(3, 'Menyelesaikan transaksi #3'),
(3, 'Membuat transaksi baru #4'),
(3, 'Menyelesaikan transaksi #4'),
(2, 'Membuat transaksi baru #5'),
(2, 'Menyelesaikan transaksi #5'),
(3, 'Membuat transaksi baru #6'),
(3, 'Menyelesaikan transaksi #6'),
(2, 'Membuat transaksi baru #7'),
(2, 'Menyelesaikan transaksi #7'),
(3, 'Membuat transaksi baru #8'),
(4, 'Login ke sistem'),
(4, 'Menambah menu baru: Nasi Goreng Spesial'),
(4, 'Menambah menu baru: Mie Goreng'),
(4, 'Menambah menu baru: Ayam Goreng'),
(5, 'Login ke sistem'),
(5, 'Menambah menu baru: Sate Ayam'),
(5, 'Menambah menu baru: Gado-gado'),
(5, 'Menambah menu baru: Soto Ayam'),
(5, 'Menambah menu baru: Rendang'),
(5, 'Menambah menu baru: Nasi Uduk'),
(5, 'Menambah menu baru: Bakso'),
(5, 'Menambah menu baru: Soto Betawi'),
(5, 'Menambah menu baru: Kopi Hitam'),
(5, 'Menambah menu baru: Kopi Susu'),
(5, 'Menambah menu baru: Teh Tarik'),
(5, 'Menambah menu baru: Es Teh'),
(5, 'Menambah menu baru: Es Jeruk'),
(5, 'Menambah menu baru: Es Campur'),
(5, 'Menambah menu baru: Jus Alpukat'),
(5, 'Menambah menu baru: Jus Mangga'),
(5, 'Menambah menu baru: Jus Melon'),
(5, 'Menambah menu baru: Jus Sirsak'); 