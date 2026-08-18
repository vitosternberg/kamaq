<?php

declare(strict_types=1);

// Instalador web de KAMAQ.
// Uso: sube el proyecto, abre https://tudominio/install.php y sigue el formulario.
// IMPORTANTE: elimina este archivo cuando termines la instalación.

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/core/functions.php';

$config = require BASE_PATH . '/app/config.php';

// ---------------------------------------------------------------
// 1) Comprobaciones previas
// ---------------------------------------------------------------
$checks = [
    ['PHP 8.0 o superior', PHP_VERSION_ID >= 80000, PHP_VERSION],
    ['Extensión pdo_mysql', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'instalada' : 'falta'],
    ['Extensión mbstring', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'instalada' : 'falta'],
];
$ready = true;
foreach ($checks as $c) {
    if (!$c[1]) {
        $ready = false;
    }
}

// ---------------------------------------------------------------
// 2) Conexión (intenta crear la base de datos si no existe)
// ---------------------------------------------------------------
$error = '';
$success = '';
$pdo = null;
$dbCreated = false;

$host = $config['db_host'];
$port = $config['db_port'];
$charset = $config['db_charset'];
$db = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];
$baseDsn = "mysql:host={$host};port={$port};charset={$charset}";

if ($ready) {
    try {
        $pdo = new PDO("{$baseDsn};dbname={$db}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e) {
        $mysqlCode = (int) ($e->errorInfo[1] ?? 0);
        $unknownDb = $mysqlCode === 1049 || stripos($e->getMessage(), 'Unknown database') !== false;
        if ($unknownDb) {
            try {
                $pdoNoDb = new PDO($baseDsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdoNoDb->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $dbCreated = true;
                $pdo = new PDO("{$baseDsn};dbname={$db}", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } catch (PDOException $e2) {
                $error = 'No se pudo conectar ni crear la base de datos <strong>' . e($db) . '</strong>: ' . e($e2->getMessage())
                    . '. Créala manualmente en cPanel y vuelve a intentar.';
            }
        } else {
            $error = 'No se pudo conectar a la base de datos: ' . e($e->getMessage())
                . '. Revisa <code>app/config.local.php</code>.';
        }
    }
}

// ---------------------------------------------------------------
// 3) ¿Ya está instalado?
// ---------------------------------------------------------------
$alreadyInstalled = false;
if ($pdo) {
    try {
        $alreadyInstalled = (bool) $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    } catch (Throwable $e) {
        $error = 'Error al verificar la base de datos: ' . e($e->getMessage());
    }
}

// ---------------------------------------------------------------
// 4) Instalar
// ---------------------------------------------------------------
if ($pdo && !$alreadyInstalled && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = (string) ($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '') !== '' ? trim($_POST['name']) : 'Administrador';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        $error = 'Necesitas un correo válido y una contraseña de al menos 6 caracteres.';
    } else {
        try {
            $sql = file_get_contents(BASE_PATH . '/database/schema.sql');
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }

            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, 'admin']);

            $success = 'Instalación completa. Ya puedes entrar a <a href="/admin">/admin</a> con ' . e($email) . '.';
            $alreadyInstalled = true;
        } catch (Throwable $e) {
            $error = 'Error durante la instalación: ' . e($e->getMessage());
        }
    }
}

// ---------------------------------------------------------------
// 5) Carpeta de subidas
// ---------------------------------------------------------------
$uploadsDir = BASE_PATH . '/public/uploads/products';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}
$uploadsWritable = is_writable($uploadsDir);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalador KAMAQ</title>
<style>
body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #f4f2ee; color: #2b2b2b; margin: 0; padding: 40px 16px; }
.box { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e0ddd6; border-radius: 10px; padding: 28px; }
h1 { margin: 0 0 18px; font-size: 22px; }
h2 { font-size: 16px; margin: 24px 0 10px; }
label { display: block; font-weight: 600; margin: 14px 0 4px; }
input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
button { width: 100%; margin-top: 20px; padding: 12px; background: #8a5a2b; color: #fff; border: 0; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; }
button:hover { background: #6d461f; }
.msg { padding: 12px 14px; border-radius: 8px; margin-bottom: 14px; }
.msg.error { background: #fdecea; color: #b3261e; border: 1px solid #f5c6c0; }
.msg.success { background: #e8f5e9; color: #1e7a2a; border: 1px solid #b7dfb9; }
.msg.warn { background: #fff7e6; color: #8a5a00; border: 1px solid #f0d9a8; }
code { background: #eee; padding: 1px 5px; border-radius: 4px; }
table.checks { width: 100%; border-collapse: collapse; margin-top: 8px; }
table.checks td { padding: 6px 4px; border-bottom: 1px solid #eee; font-size: 14px; }
.ok { color: #1e7a2a; font-weight: 700; }
.bad { color: #b3261e; font-weight: 700; }
.small { font-size: 13px; color: #6b6b6b; }
</style>
</head>
<body>
<div class="box">
<h1>Instalador KAMAQ</h1>

<h2>Requisitos</h2>
<table class="checks">
  <?php foreach ($checks as $c): ?>
    <tr>
      <td><?= e($c[0]) ?></td>
      <td style="text-align:right;"><?php if ($c[1]): ?><span class="ok">OK</span><?php else: ?><span class="bad">FALTA</span><?php endif; ?></td>
      <td style="text-align:right;" class="small"><?= e((string) $c[2]) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
<?php if ($dbCreated): ?><div class="msg warn">La base de datos <strong><?= e($db) ?></strong> no existía y fue creada.</div><?php endif; ?>

<?php if (!$ready): ?>
  <p class="msg error">El servidor no cumple los requisitos mínimos. Corrige lo indicado arriba y recarga.</p>
<?php elseif (!$pdo): ?>
  <p>Revisa las credenciales en <code>app/config.local.php</code> y recarga.</p>
<?php elseif ($alreadyInstalled): ?>
  <p class="msg success">La base de datos ya está instalada.</p>
  <p>Por seguridad, <strong>elimina este archivo <code>install.php</code></strong>.</p>
  <p><a href="/admin">Ir al panel de administración</a> · <a href="/">Ver el sitio</a></p>
<?php else: ?>
  <p>Se conectarán las tablas a la base <strong><?= e($db) ?></strong> y se creará el usuario administrador.</p>
  <form method="post">
    <label>Nombre</label>
    <input type="text" name="name" value="Administrador">
    <label>Email del administrador</label>
    <input type="email" name="email" value="admin@kamaq.cl" required>
    <label>Contraseña</label>
    <input type="password" name="password" required minlength="6">
    <button type="submit">Instalar</button>
  </form>
<?php endif; ?>

<?php if ($alreadyInstalled): ?>
  <h2>Después de instalar</h2>
  <ul class="small" style="padding-left:18px;">
    <li>Elimina <code>public/install.php</code> (por seguridad).</li>
    <li>Carpeta de subida de imágenes: <?= $uploadsWritable ? '<span class="ok">escribible</span>' : '<span class="bad">NO escribible</span> — da permisos 755/775 a <code>public/uploads/</code>' ?>.</li>
  </ul>
<?php endif; ?>
</div>
</body>
</html>
