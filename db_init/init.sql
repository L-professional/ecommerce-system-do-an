CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, price, image_url) VALUES 
('Laptop Gaming MSI', 1200.00, 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?w=400'),
('Chuột Logitech G', 50.00, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400'),
('Bàn phím cơ', 85.00, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400');

-- 1. Phân quyền tối thiểu cho Web User (Least Privilege)
-- Chỉ cấp quyền SELECT, INSERT, UPDATE, DELETE trên database ecommerce_db
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'shop_user'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `ecommerce_db`.* TO 'shop_user'@'%';

-- 2. Tài khoản dành riêng cho Monitoring Exporter (Least Privilege)
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY 'Exp_Monit0r_P@ssw0rd_2026!' WITH MAX_USER_CONNECTIONS 3;
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';

FLUSH PRIVILEGES;
