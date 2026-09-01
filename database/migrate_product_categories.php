<?php
// Migración idempotente: tabla pivote product_categories (producto ↔ categoría, muchos-a-muchos)
// con UNA categoría principal (is_primary) para breadcrumb y back-compat con products.category_id.
// Uso: php database/migrate_product_categories.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$hasTable = (bool) $pdo->query("SHOW TABLES LIKE 'product_categories'")->fetchColumn();
if (!$hasTable) {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS product_categories (
          product_id INT UNSIGNED NOT NULL,
          category_id INT UNSIGNED NOT NULL,
          is_primary TINYINT(1) NOT NULL DEFAULT 0,
          PRIMARY KEY (product_id, category_id),
          KEY idx_pc_category (category_id),
          CONSTRAINT fk_pc_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
          CONSTRAINT fk_pc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    echo "OK: tabla product_categories creada\n";
} else {
    echo "OK: product_categories ya existe (sin cambios de estructura)\n";
}

// Backfill idempotente: cada producto con category_id existente conserva esa categoría como principal.
$pdo->exec(
    'INSERT IGNORE INTO product_categories (product_id, category_id, is_primary)
     SELECT id, category_id, 1 FROM products WHERE category_id IS NOT NULL'
);
echo "OK: backfill de product_categories desde products.category_id\n";
