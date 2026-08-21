<?php
// Migración idempotente: agrega la columna products.is_bestseller (sección "Destacados del mes").
// Uso: php database/migrate_bestsellers.php  (bórrala tras ejecutarla si lo prefieres)
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$has = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_bestseller'")->fetch();
if (!$has) {
    $pdo->exec("ALTER TABLE products ADD COLUMN is_bestseller TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured");
    echo "OK: columna products.is_bestseller agregada\n";
} else {
    echo "OK: columna products.is_bestseller ya existe (sin cambios)\n";
}
