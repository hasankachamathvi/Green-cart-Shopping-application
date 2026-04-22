-- Create Database
CREATE DATABASE IF NOT EXISTS shopping_cart_db;
USE shopping_cart_db;

-- =========================
-- 1. USERS TABLE
-- =========================
CREATE TABLE users (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(255) NULL,
	login_type ENUM('google', 'facebook', 'passkey', 'manual') DEFAULT 'manual',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- 2. CATEGORIES TABLE
-- =========================
CREATE TABLE categories (
	category_id INT AUTO_INCREMENT PRIMARY KEY,
	category_name VARCHAR(100) NOT NULL
);

-- =========================
-- 3. PRODUCTS TABLE
-- =========================
CREATE TABLE products (
	product_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(150) NOT NULL,
	description TEXT,
	price DECIMAL(10,2) NOT NULL,
	image_url VARCHAR(255),
	category_id INT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

	FOREIGN KEY (category_id) REFERENCES categories(category_id)
	ON DELETE SET NULL
);

-- =========================
-- 4. CARTS TABLE
-- =========================
CREATE TABLE carts (
	cart_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNIQUE,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

	FOREIGN KEY (user_id) REFERENCES users(user_id)
	ON DELETE CASCADE
);

-- =========================
-- 5. CART ITEMS TABLE
-- =========================
CREATE TABLE cart_items (
	cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
	cart_id INT,
	product_id INT,
	quantity INT NOT NULL DEFAULT 1,

	FOREIGN KEY (cart_id) REFERENCES carts(cart_id)
	ON DELETE CASCADE,

	FOREIGN KEY (product_id) REFERENCES products(product_id)
	ON DELETE CASCADE,

	UNIQUE(cart_id, product_id)
);

-- =========================
-- 6. ORDERS TABLE (Future)
-- =========================
CREATE TABLE orders (
	order_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT,
	total_amount DECIMAL(10,2),
	status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
	order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

	FOREIGN KEY (user_id) REFERENCES users(user_id)
	ON DELETE CASCADE
);

-- =========================
-- 7. ORDER ITEMS TABLE (Future)
-- =========================
CREATE TABLE order_items (
	order_item_id INT AUTO_INCREMENT PRIMARY KEY,
	order_id INT,
	product_id INT,
	quantity INT,
	price DECIMAL(10,2),

	FOREIGN KEY (order_id) REFERENCES orders(order_id)
	ON DELETE CASCADE,

	FOREIGN KEY (product_id) REFERENCES products(product_id)
	ON DELETE CASCADE
);


-- =========================
-- INDEXES (Performance)
-- =========================
CREATE INDEX idx_product_category ON products(category_id);
CREATE INDEX idx_cart_user ON carts(user_id);
