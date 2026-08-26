<?php
// Migración idempotente: unidad de venta en productos.
// Uso: php database/migrate_product_unit.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$has = $pdo->query("SHOW COLUMNS FROM products LIKE 'unit'")->fetch();
if (!$has) {
    $pdo->exec("ALTER TABLE products ADD COLUMN unit VARCHAR(20) NOT NULL DEFAULT 'unidad' AFTER height");
}

echo "OK: unidad de venta agregada a productos\n";
