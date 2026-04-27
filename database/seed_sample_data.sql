-- =========================
-- SAMPLE DATA FOR TESTING
-- =========================
USE shopping_cart_db;

-- =========================
-- 1. INSERT SAMPLE USERS
-- =========================
INSERT INTO users (name, email, password, login_type, created_at) VALUES
('Test Admin', 'admin@greencart.com', '$2y$10$yFP9zhXF2pQtyJgtndVyTOraAGBoINoqjsKa3q7x.y3blVNa6ZxMa', 'manual', NOW()),
('John Doe', 'john@example.com', '$2y$10$yFP9zhXF2pQtyJgtndVyTOraAGBoINoqjsKa3q7x.y3blVNa6ZxMa', 'manual', NOW()),
('Jane Smith', 'jane@example.com', '$2y$10$yFP9zhXF2pQtyJgtndVyTOraAGBoINoqjsKa3q7x.y3blVNa6ZxMa', 'manual', NOW()),
('Ahmed Ali', 'ahmed@example.com', '$2y$10$yFP9zhXF2pQtyJgtndVyTOraAGBoINoqjsKa3q7x.y3blVNa6ZxMa', 'google', NOW());

-- =========================
-- 2. INSERT SAMPLE CATEGORIES
-- =========================
INSERT INTO categories (category_name) VALUES
('Vegetables'),
('Fruits'),
('Cakes & Pastries'),
('Biscuits & Snacks'),
('Dairy & Milk'),
('Beverages');

-- =========================
-- 3. INSERT SAMPLE PRODUCTS
-- =========================

-- Vegetables (category_id = 1)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Fresh Tomatoes', 'Ripe red tomatoes, perfect for salads and cooking', 120.00, 'tomatoes.jpg', 1, NOW()),
('Green Spinach', 'Organic fresh spinach leaves, loaded with iron', 80.00, 'spinach.jpg', 1, NOW()),
('Carrots Bundle', 'Sweet orange carrots, great for health and taste', 100.00, 'carrots.jpg', 1, NOW()),
('Capsicum Mix', 'Bell peppers in red, yellow, and green colors', 150.00, 'capsicum.jpg', 1, NOW()),
('Broccoli Fresh', 'Green broccoli crowns, rich in nutrients', 110.00, 'broccoli.jpg', 1, NOW()),
('Onions (1kg)', 'Golden onions for everyday cooking', 60.00, 'onions.jpg', 1, NOW());

-- Fruits (category_id = 2)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Bananas (1kg)', 'Golden bananas, perfect source of potassium', 90.00, 'bananas.jpg', 2, NOW()),
('Apples (1kg)', 'Red apples, crispy and sweet', 250.00, 'apples.jpg', 2, NOW()),
('Oranges (1kg)', 'Fresh juicy oranges, rich in Vitamin C', 140.00, 'oranges.jpg', 2, NOW()),
('Grapes Bundle', 'Sweet green grapes, perfect for snacking', 200.00, 'grapes.jpg', 2, NOW()),
('Mango (per piece)', 'King of fruits, aromatic and delicious', 120.00, 'mango.jpg', 2, NOW()),
('Papaya (per kg)', 'Tropical fruit with sweet orange flesh', 80.00, 'papaya.jpg', 2, NOW()),
('Strawberries', 'Fresh red strawberries, sweet and juicy', 350.00, 'strawberries.jpg', 2, NOW());

-- Cakes & Pastries (category_id = 3)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Chocolate Cake (500g)', 'Rich chocolate cake with chocolate frosting', 450.00, 'chocolate-cake.jpg', 3, NOW()),
('Vanilla Cake (500g)', 'Classic vanilla cake, light and fluffy', 400.00, 'vanilla-cake.jpg', 3, NOW()),
('Red Velvet Cake', 'Elegant red velvet cake with cream cheese frosting', 500.00, 'red-velvet.jpg', 3, NOW()),
('Cheesecake Slice (per 100g)', 'Creamy New York style cheesecake', 200.00, 'cheesecake.jpg', 3, NOW()),
('Strawberry Tart', 'Fresh strawberry tart with pastry cream', 350.00, 'strawberry-tart.jpg', 3, NOW());

-- Biscuits & Snacks (category_id = 4)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Chocolate Biscuits (200g)', 'Crispy chocolate biscuits for tea time', 80.00, 'choco-biscuits.jpg', 4, NOW()),
('Digestive Biscuits (250g)', 'Whole wheat digestive biscuits', 70.00, 'digestive.jpg', 4, NOW()),
('Wafers (150g)', 'Thin crispy wafers with chocolate coating', 120.00, 'wafers.jpg', 4, NOW()),
('Peanut Chips (200g)', 'Roasted peanut snack chips', 100.00, 'peanut-chips.jpg', 4, NOW()),
('Potato Chips (150g)', 'Crispy salted potato chips', 90.00, 'potato-chips.jpg', 4, NOW());

-- Dairy & Milk (category_id = 5)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Full Cream Milk (1L)', 'Fresh full cream milk, pasteurized', 140.00, 'milk.jpg', 5, NOW()),
('Yogurt (500ml)', 'Creamy natural yogurt', 120.00, 'yogurt.jpg', 5, NOW()),
('Cheese (200g)', 'Hard cheese slices, perfect for sandwiches', 250.00, 'cheese.jpg', 5, NOW()),
('Butter (250g)', 'Pure butter for cooking and baking', 300.00, 'butter.jpg', 5, NOW()),
('Ghee (500ml)', 'Pure clarified butter for cooking', 450.00, 'ghee.jpg', 5, NOW());

-- Beverages (category_id = 6)
INSERT INTO products (name, description, price, image_url, category_id, created_at) VALUES
('Orange Juice (1L)', 'Fresh squeezed orange juice, no added sugar', 180.00, 'orange-juice.jpg', 6, NOW()),
('Apple Juice (1L)', 'Naturally sweet apple juice', 170.00, 'apple-juice.jpg', 6, NOW()),
('Green Tea (20 bags)', 'Organic green tea for health benefits', 200.00, 'green-tea.jpg', 6, NOW()),
('Coffee Powder (200g)', 'Premium ground coffee for daily brew', 350.00, 'coffee.jpg', 6, NOW());

-- =========================
-- 4. INSERT SAMPLE CARTS & CART ITEMS (for testing)
-- =========================

-- Create carts for users 1, 2, 3, 4
INSERT INTO carts (user_id, created_at) VALUES
(1, NOW()),
(2, NOW()),
(3, NOW()),
(4, NOW());

-- Add some items to John's cart (user_id = 2, cart_id = 2)
INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(2, 1, 2),   -- 2x Fresh Tomatoes
(2, 7, 1),   -- 1x Bananas
(2, 23, 1);  -- 1x Chocolate Cake

-- Add some items to Jane's cart (user_id = 3, cart_id = 3)
INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(3, 3, 1),   -- 1x Carrots Bundle
(3, 14, 2);  -- 2x Mango

-- =========================
-- 5. INSERT SAMPLE ORDERS (for profile page testing)
-- =========================

INSERT INTO orders (user_id, total_amount, status, order_date) VALUES
(2, 850.00, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 1200.00, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 450.00, 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 2100.00, 'completed', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Insert order items for these orders
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
-- Order 1 (user 2, Rs. 850)
(1, 1, 2, 120.00),   -- 2x Fresh Tomatoes
(1, 7, 1, 90.00),    -- 1x Bananas
(1, 23, 1, 450.00),  -- 1x Chocolate Cake

-- Order 2 (user 2, Rs. 1200)
(2, 10, 1, 250.00),  -- 1x Apples
(2, 26, 1, 200.00),  -- 1x Green Tea
(2, 3, 3, 100.00),   -- 3x Carrots Bundle
(2, 35, 1, 140.00),  -- 1x Full Cream Milk

-- Order 3 (user 3, Rs. 450)
(3, 14, 1, 120.00),  -- 1x Mango
(3, 31, 1, 200.00),  -- 1x Cheese
(3, 4, 1, 150.00),   -- 1x Capsicum Mix

-- Order 4 (user 4, Rs. 2100)
(4, 23, 2, 450.00),  -- 2x Chocolate Cake
(4, 24, 1, 400.00),  -- 1x Vanilla Cake
(4, 7, 5, 90.00),    -- 5x Bananas
(4, 26, 3, 200.00);  -- 3x Green Tea

-- =========================
-- VERIFICATION QUERIES
-- =========================
-- Run these to check if data was inserted correctly:
-- SELECT COUNT(*) as total_products FROM products;
-- SELECT COUNT(*) as total_categories FROM categories;
-- SELECT * FROM products LIMIT 5;
-- SELECT * FROM categories;
-- SELECT * FROM users;
