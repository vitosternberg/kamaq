<?php

declare(strict_types=1);

// Instalador de KAMAQ — ejecutar por CLI:  php database/install.php
if (PHP_SAPI !== 'cli') {
    exit("Este script se ejecuta por línea de comandos: php database/install.php\n");
}

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/app/config.php';

echo "Instalador KAMAQ\n";
echo "----------------\n";

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db_host'],
    $config['db_port'],
    $config['db_name'],
    $config['db_charset']
);

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Error de conexión: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Revisa las credenciales en app/config.local.php\n");
    exit(1);
}

$schemaFile = BASE_PATH . '/database/schema.sql';
if (!is_file($schemaFile)) {
    fwrite(STDERR, "No se encontró database/schema.sql\n");
    exit(1);
}

$sql = file_get_contents($schemaFile);
$sql = preg_replace('/^\s*--.*$/m', '', $sql);

$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }
    $pdo->exec($statement);
}
echo "OK - Esquema creado (tablas + datos base).\n";

echo "\nCrear usuario administrador:\n";

echo "Nombre [Administrador]: ";
$name = trim((string) fgets(STDIN));
if ($name === '') {
    $name = 'Administrador';
}

echo "Email [admin@kamaq.cl]: ";
$email = trim((string) fgets(STDIN));
if ($email === '') {
    $email = 'admin@kamaq.cl';
}

echo "Contraseña [admin123]: ";
$pass = trim((string) fgets(STDIN));
if ($pass === '') {
    $pass = 'admin123';
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)'
);
$stmt->execute([$name, $email, $hash, 'admin']);

echo "\nOK - Usuario administrador: {$email}\n";
echo "OK - Instalación completa. Accede a /admin\n";
