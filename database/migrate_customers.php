<?php
// Migración idempotente (segura de re-ejecutar). Bórrala tras ejecutarla.
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS customers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
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
  KEY idx_customers_verify_token (verify_token),
  KEY idx_customers_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$hasCustomerId = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_id'")->fetch();
if (!$hasCustomerId) {
    $pdo->exec("ALTER TABLE orders ADD COLUMN customer_id INT UNSIGNED NULL AFTER id");
}

$pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('shipping_rm_price', '3990'),
  ('shipping_free_threshold', '15000'),
  ('shipping_express_price', '4990'),
  ('shipping_outside_price', '6990')");

echo "OK: migración aplicada (customers, orders.customer_id, settings de envío)\n";
