<?php
// Migración idempotente: flag de control de stock (track_stock) en productos.
// Uso: php database/migrate_product_track_stock.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$has = $pdo->query("SHOW COLUMNS FROM products LIKE 'track_stock'")->fetch();
if (!$has) {
    $pdo->exec("ALTER TABLE products ADD COLUMN track_stock TINYINT(1) NOT NULL DEFAULT 1 AFTER stock");
}

echo "OK: track_stock agregado a products\n";
