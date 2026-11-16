-- Little Things demo shop database schema and sample catalog.
-- In phpMyAdmin: create/select your database, open Import, then import this file.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  emoji VARCHAR(20) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 10,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order_id (order_id),
  KEY idx_order_items_product_id (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name, category, description, price, emoji, stock) VALUES
('Everyday Canvas Tote','Accessories','Your grab-and-go bag for market mornings, library runs, and everything in between.',24.00,'👜',18),
('Sunday Ceramic Mug','Home goods','A handmade-feeling stoneware mug made for slow coffee and long conversations.',18.50,'☕',12),
('Field Notes Set','Stationery','Three pocket-sized notebooks with recycled paper for ideas on the move.',12.00,'📓',24),
('Little Sun Planter','Home goods','A cheerful ceramic home for your favorite little leafy friend.',28.00,'🪴',9),
('Weekend Candle','Home goods','A warm cedar and citrus candle poured in a reusable amber glass jar.',22.00,'🕯️',15),
('Good Day Cap','Accessories','A soft cotton cap for bright days, bad hair days, and all the rest.',30.00,'🧢',10);
