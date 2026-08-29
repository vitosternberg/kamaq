<?php
// Migración idempotente: columnas Transbank (pago online) en orders.
// Uso: php database/migrate_transbank.php
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
    'transbank_token'              => "ADD COLUMN transbank_token VARCHAR(64) NULL AFTER total",
    'transbank_authorization_code' => "ADD COLUMN transbank_authorization_code VARCHAR(32) NULL AFTER transbank_token",
    'transbank_payment_type'       => "ADD COLUMN transbank_payment_type VARCHAR(32) NULL AFTER transbank_authorization_code",
    'transbank_transaction_date'   => "ADD COLUMN transbank_transaction_date DATETIME NULL AFTER transbank_payment_type",
    'transbank_installments'       => "ADD COLUMN transbank_installments TINYINT UNSIGNED NULL AFTER transbank_transaction_date",
    'reserved_at'                  => "ADD COLUMN reserved_at DATETIME NULL AFTER transbank_installments",
    'expires_at'                   => "ADD COLUMN expires_at DATETIME NULL AFTER reserved_at",
    'paid_at'                      => "ADD COLUMN paid_at DATETIME NULL AFTER expires_at",
];

foreach ($columns as $col => $ddl) {
    $has = $pdo->query("SHOW COLUMNS FROM orders LIKE '{$col}'")->fetch();
    if (!$has) {
        $pdo->exec("ALTER TABLE orders {$ddl}");
    }
}

echo "OK: columnas Transbank agregadas a orders\n";
