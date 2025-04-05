CREATE DATABASE IF NOT EXISTS cafe_db;
USE cafe_db;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('kasir', 'manajer', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category ENUM('makanan', 'minuman', 'snack') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    status ENUM('tersedia', 'tidak_tersedia') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_number INT NOT NULL,
    cashier_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert data default admin
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$OkupL.oS16TYw/NbGGZl7OqFW4H.RV/ZhvhJyWFafJB3rNOLvwQES', 'admin');

-- Insert data dummy users
INSERT INTO users (username, password, role) VALUES 
('kasir1', '$2y$10$5iKNE4E/uC6iEzyPKpy6B.y1DmwNQd4zUaGthz97KcYEM....RWSO', 'kasir'),
('kasir2', '$2y$10$ZyC3MGVrsnVI3AiohvpUZeqb5g5n/DEml1ZJ9oUCkijhpyjCwaJBq', 'kasir'),
('manajer1', '$2y$10$vL7wbXV4uYdOcBaMIQWUseyTCM60ZtC5vcSlUsYzKd4IikquR7BXO', 'manajer'),
('manajer2', '$2y$10$2tQwNlEM21D.C5AwKSg8jOrw8Z0vkbVkVLx6Ec3h8ZRaz2wwrIBWS', 'manajer');

-- Insert data dummy menu
INSERT INTO menu (name, category, price, stock) VALUES
('Nasi Goreng', 'makanan', 15000, 100),
('Mie Goreng', 'makanan', 12000, 100),
('Es Teh', 'minuman', 3000, 100),
('Es Jeruk', 'minuman', 4000, 100),
('Kentang Goreng', 'snack', 8000, 100);

-- Insert data dummy orders
INSERT INTO orders (table_number, cashier_id, total_amount, status) VALUES
(1, 2, 18000, 'completed'),
(2, 2, 22000, 'completed'),
(3, 3, 15000, 'pending');

-- Insert data dummy order_items
INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES
(1, 1, 1, 15000),
(1, 3, 1, 3000),
(2, 2, 1, 12000),
(2, 4, 1, 4000),
(2, 5, 1, 8000),
(3, 1, 1, 15000);

-- Insert data dummy activity_logs
INSERT INTO activity_logs (user_id, activity) VALUES
(2, 'Login ke sistem'),
(2, 'Membuat transaksi #1'),
(2, 'Menyelesaikan transaksi #1'),
(3, 'Login ke sistem'),
(3, 'Membuat transaksi #2'),
(3, 'Membuat transaksi #3');

-- Insert data dummy notifications
INSERT INTO notifications (user_id, message) VALUES
(2, 'Transaksi baru #3 dari meja 3'),
(3, 'Menu Nasi Goreng stok menipis'); 