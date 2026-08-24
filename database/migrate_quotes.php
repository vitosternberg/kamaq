<?php
// Migración idempotente: cotizaciones (quotes) + líneas (quote_items).
// Uso: php database/migrate_quotes.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS quotes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS quote_items (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "OK: migración de cotizaciones aplicada\n";
