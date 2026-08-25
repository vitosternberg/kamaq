<?php
// Migración idempotente: peso y medidas (para envío) en productos.
// Uso: php database/migrate_product_shipping.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$columns = [
    'weight' => "ADD COLUMN weight DECIMAL(10,3) NULL AFTER tax_id",
    'length' => "ADD COLUMN length DECIMAL(10,2) NULL AFTER weight",
    'width'  => "ADD COLUMN width DECIMAL(10,2) NULL AFTER length",
    'height' => "ADD COLUMN height DECIMAL(10,2) NULL AFTER width",
];

foreach ($columns as $col => $ddl) {
    $has = $pdo->query("SHOW COLUMNS FROM products LIKE '{$col}'")->fetch();
    if (!$has) {
        $pdo->exec("ALTER TABLE products {$ddl}");
    }
}

echo "OK: peso y medidas agregados a productos\n";
