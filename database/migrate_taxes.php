<?php
// Migración idempotente: impuestos (taxes) + costo/margen/impuesto en productos
// + snapshot de costo/tasa en order_items + columna tax en orders.
// Uso: php database/migrate_taxes.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS taxes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  rate DECIMAL(6,2) NOT NULL DEFAULT 0,
  type VARCHAR(40) NOT NULL DEFAULT 'IVA',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("INSERT IGNORE INTO taxes (id, name, rate, type, is_active, sort_order) VALUES (1, 'IVA', 19.00, 'IVA', 1, 1)");

$columns = [
    'products' => [
        'cost' => "ALTER TABLE products ADD COLUMN cost DECIMAL(12,2) NULL AFTER is_bestseller",
        'margin_percent' => "ALTER TABLE products ADD COLUMN margin_percent DECIMAL(6,2) NULL AFTER cost",
        'tax_id' => "ALTER TABLE products ADD COLUMN tax_id INT UNSIGNED NULL AFTER margin_percent",
    ],
    'orders' => [
        'tax' => "ALTER TABLE orders ADD COLUMN tax DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER subtotal",
    ],
    'order_items' => [
        'cost' => "ALTER TABLE order_items ADD COLUMN cost DECIMAL(12,2) NULL AFTER subtotal",
        'tax_rate' => "ALTER TABLE order_items ADD COLUMN tax_rate DECIMAL(6,2) NULL AFTER cost",
    ],
];

foreach ($columns as $table => $defs) {
    $existing = [];
    foreach ($pdo->query("SHOW COLUMNS FROM {$table}") as $row) {
        $existing[] = $row['Field'];
    }
    foreach ($defs as $name => $sql) {
        if (!in_array($name, $existing, true)) {
            $pdo->exec($sql);
            echo "OK: {$table}.{$name} agregado\n";
        } else {
            echo "OK: {$table}.{$name} ya existe\n";
        }
    }
}

echo "OK: migración de impuestos/costos aplicada\n";
