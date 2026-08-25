CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  description TEXT NULL,
  meta_title VARCHAR(160) NULL,
  meta_description VARCHAR(255) NULL,
  image VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  KEY idx_categories_parent (parent_id),
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL,
  sku VARCHAR(64) NULL,
  short_description VARCHAR(255) NULL,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  sale_price DECIMAL(12,2) NULL,
  stock INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  cost DECIMAL(12,2) NULL,
  margin_percent DECIMAL(6,2) NULL,
  tax_id INT UNSIGNED NULL,
  weight DECIMAL(10,3) NULL,
  length DECIMAL(10,2) NULL,
  width DECIMAL(10,2) NULL,
  height DECIMAL(10,2) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  meta_title VARCHAR(160) NULL,
  meta_description VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_slug (slug),
  KEY idx_products_category (category_id),
  KEY idx_products_active (is_active),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  filename VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_product_images_product (product_id),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS companies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rut VARCHAR(20) NOT NULL,
  razon_social VARCHAR(160) NOT NULL,
  address VARCHAR(255) NULL,
  email VARCHAR(160) NULL,
  phone VARCHAR(40) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_companies_rut (rut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  rut VARCHAR(20) NULL,
  company_id INT UNSIGNED NULL,
  email VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  region VARCHAR(120) NULL,
  is_rm TINYINT(1) NOT NULL DEFAULT 0,
  city VARCHAR(120) NULL,
  address VARCHAR(255) NULL,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  verify_token VARCHAR(64) NULL,
  reset_token VARCHAR(64) NULL,
  reset_token_expires DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_customers_email (email),
  UNIQUE KEY uq_customers_rut (rut),
  KEY idx_customers_company (company_id),
  KEY idx_customers_verify_token (verify_token),
  KEY idx_customers_reset_token (reset_token),
  CONSTRAINT fk_customers_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NULL,
  order_number VARCHAR(32) NOT NULL,
  customer_name VARCHAR(160) NOT NULL,
  customer_email VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(40) NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  region VARCHAR(120) NULL,
  notes TEXT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_method VARCHAR(120) NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(40) NULL,
  payment_status VARCHAR(24) NOT NULL DEFAULT 'pendiente',
  status VARCHAR(24) NOT NULL DEFAULT 'pendiente',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_number (order_number),
  KEY idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(180) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 1,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  cost DECIMAL(12,2) NULL,
  tax_rate DECIMAL(6,2) NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  quote_number VARCHAR(32) NOT NULL,
  customer_rut VARCHAR(32) NULL,
  customer_company VARCHAR(160) NOT NULL,
  customer_address VARCHAR(255) NULL,
  customer_email VARCHAR(160) NULL,
  customer_phone VARCHAR(40) NULL,
  contact_person VARCHAR(120) NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_rate DECIMAL(6,2) NOT NULL DEFAULT 19.00,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'borrador',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_quotes_number (quote_number),
  KEY idx_quotes_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quote_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  quote_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(180) NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 1,
  line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_quote_items_quote (quote_id),
  CONSTRAINT fk_quote_items_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipping_methods (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS taxes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  rate DECIMAL(6,2) NOT NULL DEFAULT 0,
  type VARCHAR(40) NOT NULL DEFAULT 'IVA',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('site_name', 'KAMAQ'),
  ('currency', 'CLP'),
  ('currency_symbol', '$'),
  ('currency_decimals', '0'),
  ('contact_email', 'contacto@kamaq.cl'),
  ('contact_phone', ''),
  ('whatsapp', ''),
  ('shipping_default', '0'),
  ('low_stock_threshold', '5'),
  ('shipping_rm_price', '3990'),
  ('shipping_free_threshold', '15000'),
  ('shipping_express_price', '4990'),
  ('shipping_outside_price', '6990');

INSERT IGNORE INTO categories (id, parent_id, name, slug, sort_order, is_active) VALUES
  (1, NULL, 'Regalos Corporativos', 'regalos-corporativos', 1, 1),
  (2, NULL, 'Bautizos', 'bautizos', 2, 1),
  (3, NULL, 'Baby Shower', 'baby-shower', 3, 1),
  (4, NULL, 'Matrimonios', 'matrimonios', 4, 1),
  (5, NULL, 'Cumpleaños', 'cumpleanos', 5, 1),
  (6, NULL, 'Cajas de Vino', 'cajas-de-vino', 6, 1),
  (7, NULL, 'Joyeros', 'joyeros', 7, 1);

INSERT IGNORE INTO shipping_methods (id, name, price, is_active, sort_order) VALUES
  (1, 'Gratis', 0, 1, 1),
  (2, 'Express', 4990, 1, 2),
  (3, 'Dentro de la RM', 3990, 1, 3),
  (4, 'Fuera de la RM', 6990, 1, 4);

INSERT IGNORE INTO taxes (id, name, rate, type, is_active, sort_order) VALUES
  (1, 'IVA', 19.00, 'IVA', 1, 1);
