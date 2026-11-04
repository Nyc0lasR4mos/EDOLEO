CREATE DATABASE IF NOT EXISTS edoleo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edoleo;

CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(80) NOT NULL UNIQUE,
 slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id INT UNSIGNED NOT NULL,
 name VARCHAR(180) NOT NULL,
 brand VARCHAR(120) NOT NULL DEFAULT '',
 description TEXT NOT NULL,
 price DECIMAL(10,2) NOT NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0,
 image VARCHAR(500) NOT NULL,
 featured TINYINT(1) NOT NULL DEFAULT 0,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_products_category FOREIGN KEY(category_id) REFERENCES categories(id),
 INDEX idx_products_catalog (active, featured, category_id),
 CONSTRAINT chk_products_price CHECK(price >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 payment_method ENUM('PIX','Boleto','Cartão') NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'confirmado',
 CONSTRAINT fk_orders_user FOREIGN KEY(user_id) REFERENCES users(id),
 INDEX idx_orders_user_date(user_id,created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 unit_price DECIMAL(10,2) NOT NULL,
 CONSTRAINT fk_order_items_order FOREIGN KEY(order_id) REFERENCES orders(id),
 CONSTRAINT fk_order_items_product FOREIGN KEY(product_id) REFERENCES products(id),
 CONSTRAINT chk_order_item_quantity CHECK(quantity > 0),
 INDEX idx_order_items_order(order_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories(name,slug) VALUES
('Ternos','ternos'),('Blazers','blazers'),('Vestidos','vestidos'),('Camisas','camisas'),('Acessórios','acessorios');

UPDATE categories
SET name = CONVERT(0x4163657373C3B372696F73 USING utf8mb4)
WHERE slug = 'acessorios';

INSERT INTO products(category_id,name,brand,description,price,stock,image,featured) VALUES
 ((SELECT id FROM categories WHERE slug='ternos'),'Terno de lã fria italiano','Armani','Terno de lã fria em tom grafite, corte clássico e caimento impecável. Revisado e pronto para um novo capítulo.',789.00,1,'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=850&q=85',1),
 ((SELECT id FROM categories WHERE slug='blazers'),'Blazer estruturado em linho','Max Mara','Blazer de linho em tom areia, leve e versátil. Uma peça atemporal para compor looks elegantes.',429.00,1,'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=850&q=85',1),
 ((SELECT id FROM categories WHERE slug='vestidos'),'Vestido midi de crepe','Hugo Boss','Vestido midi de crepe com caimento fluido e acabamento refinado. Elegância sem esforço.',369.00,1,'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=850&q=85',1),
 ((SELECT id FROM categories WHERE slug='ternos'),'Costume slim azul profundo','Vila Romana','Costume de alfaiataria em azul-marinho, corte slim e tecido de toque macio. Em excelente estado.',519.00,1,'https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=850&q=85',1),
 ((SELECT id FROM categories WHERE slug='blazers'),'Blazer de alfaiataria cruzado','Ralph Lauren','Blazer cruzado em lã mista com lapela clássica. Um essencial para ocasiões que pedem presença.',449.00,1,'https://images.unsplash.com/photo-1592878904946-b3cd8ae243d0?auto=format&fit=crop&w=850&q=85',0),
 ((SELECT id FROM categories WHERE slug='camisas'),'Camisa de seda marfim','Le Lis Blanc','Seda natural com brilho delicado e toque suave. Uma peça versátil de acabamento premium.',229.00,1,'https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&fit=crop&w=850&q=85',0);
