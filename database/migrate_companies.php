<?php
// Migración idempotente: tabla companies + rut/company_id en customers.
// Uso: php database/migrate_companies.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS companies (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$hasRut = $pdo->query("SHOW COLUMNS FROM customers LIKE 'rut'")->fetch();
if (!$hasRut) {
    $pdo->exec("ALTER TABLE customers ADD COLUMN rut VARCHAR(20) NULL AFTER name, ADD UNIQUE KEY uq_customers_rut (rut)");
}

$hasCompanyId = $pdo->query("SHOW COLUMNS FROM customers LIKE 'company_id'")->fetch();
if (!$hasCompanyId) {
    $pdo->exec("ALTER TABLE customers ADD COLUMN company_id INT UNSIGNED NULL AFTER rut, ADD KEY idx_customers_company (company_id), ADD CONSTRAINT fk_customers_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL");
}

echo "OK: migración de empresas aplicada\n";
