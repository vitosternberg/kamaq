<?php
// Migración: regenera el SKU de todos los productos desde su id auto-incremental.
// Formato: SKU-000123. Idempotente (mismo resultado al re-ejecutar).
// Uso: php database/migrate_sku_autogen.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("UPDATE products SET sku = CONCAT('SKU-', LPAD(id, 6, '0'))");

echo "OK: SKU regenerados\n";
