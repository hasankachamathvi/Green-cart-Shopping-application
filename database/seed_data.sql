USE shopping_cart_db;

-- Categories (insert only if missing)
INSERT INTO categories (category_name)
SELECT 'Vegetables' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Vegetables'
);

INSERT INTO categories (category_name)
SELECT 'Fruits' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Fruits'
);

INSERT INTO categories (category_name)
SELECT 'Bakery' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Bakery'
);

-- Products (insert only if missing by name)
INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Carrot', 'Fresh organic carrots', 180.00, 'carrot.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Vegetables'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Carrot');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Tomato', 'Farm-picked red tomatoes', 220.00, 'tomato.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Vegetables'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Tomato');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Broccoli', 'Crisp green broccoli', 350.00, 'broccoli.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Vegetables'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Broccoli');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Apple', 'Sweet and crunchy apples', 300.00, 'apple.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Fruits'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Apple');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Banana', 'Naturally ripe bananas', 160.00, 'banana.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Fruits'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Banana');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Mango', 'Seasonal juicy mangoes', 450.00, 'mango.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Fruits'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Mango');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Butter Biscuit', 'Freshly baked butter biscuits', 280.00, 'biscuit.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Bakery'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Butter Biscuit');

INSERT INTO products (name, description, price, image_url, category_id)
SELECT 'Chocolate Cake', 'Soft chocolate cake slice', 520.00, 'cake.jpg', c.category_id
FROM categories c
WHERE c.category_name = 'Bakery'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Chocolate Cake');
