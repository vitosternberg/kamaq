<?php
// Migración idempotente: tabla dtes (boleta/factura electrónica vía LibreDTE).
// Uso: php database/migrate_dtes.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$exists = $pdo->query("SHOW TABLES LIKE 'dtes'")->fetch();
if (!$exists) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS dtes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  tipo TINYINT UNSIGNED NOT NULL,
  folio BIGINT UNSIGNED NULL,
  track_id VARCHAR(32) NULL,
  codigo VARCHAR(64) NULL,
  estado VARCHAR(24) NOT NULL DEFAULT 'pendiente',
  glosa VARCHAR(255) NULL,
  pdf_url VARCHAR(255) NULL,
  certificacion TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dtes_order_tipo (order_id, tipo),
  KEY idx_dtes_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

echo "OK: tabla dtes creada\n";
