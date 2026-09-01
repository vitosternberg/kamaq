<?php
// Migración idempotente: medio de pago por transferencia y documento (boleta/factura).
// - companies.giro: giro de la empresa (obligatorio para factura).
// - customers.doc_type: 'boleta' | 'factura' (fijo por cliente).
// - orders.doc_type/doc_rut/doc_company/doc_giro: snapshot del documento al comprar.
// - settings: datos bancarios para pagos por transferencia.
// Uso: php database/migrate_payment_doc.php
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'], $config['db_port'], $config['db_name'], $config['db_charset']
);
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$has = $pdo->query("SHOW COLUMNS FROM companies LIKE 'giro'")->fetch();
if (!$has) {
    $pdo->exec("ALTER TABLE companies ADD COLUMN giro VARCHAR(160) NULL AFTER razon_social");
}

$has = $pdo->query("SHOW COLUMNS FROM customers LIKE 'doc_type'")->fetch();
if (!$has) {
    $pdo->exec("ALTER TABLE customers ADD COLUMN doc_type VARCHAR(12) NOT NULL DEFAULT 'boleta' AFTER company_id");
}

// Backfill: clientes empresa existentes pasan a factura.
$pdo->exec("UPDATE customers SET doc_type = 'factura' WHERE company_id IS NOT NULL AND doc_type = 'boleta'");

$columns = [
    'doc_type'    => "ADD COLUMN doc_type VARCHAR(12) NULL AFTER payment_method",
    'doc_rut'     => "ADD COLUMN doc_rut VARCHAR(20) NULL AFTER doc_type",
    'doc_company' => "ADD COLUMN doc_company VARCHAR(160) NULL AFTER doc_rut",
    'doc_giro'    => "ADD COLUMN doc_giro VARCHAR(160) NULL AFTER doc_company",
];

foreach ($columns as $col => $ddl) {
    $has = $pdo->query("SHOW COLUMNS FROM orders LIKE '{$col}'")->fetch();
    if (!$has) {
        $pdo->exec("ALTER TABLE orders {$ddl}");
    }
}

$pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('transfer_holder', 'KAMAQ COMERCIAL Y SERVICIOS DE DISENO SPA'),
  ('transfer_rut', '78.479.102-5'),
  ('transfer_bank', 'Banco Bci / Mach'),
  ('transfer_account_type', 'Cuenta corriente'),
  ('transfer_account_number', '69588505'),
  ('transfer_email', 'ereyes@kamaq.cl')");

echo "OK: pago por transferencia y documento (boleta/factura) habilitados\n";
