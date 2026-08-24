<?php
$pageTitle = $pageTitle ?? 'Panel';
$isAuth = \App\Core\Auth::check();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> — <?= e(config('app_name', 'KAMAQ')) ?></title>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<?php if ($isAuth): ?>
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <div class="brand"><?= e(config('app_name', 'KAMAQ')) ?></div>
    <a href="<?= url('admin') ?>">Dashboard</a>
    <a href="<?= url('admin/productos') ?>">Productos</a>
    <a href="<?= url('admin/inventario') ?>">Inventario</a>
    <a href="<?= url('admin/categorias') ?>">Categorías</a>
    <a href="<?= url('admin/pedidos') ?>">Pedidos</a>
    <a href="<?= url('admin/cotizaciones') ?>">Cotizaciones</a>
    <a href="<?= url('admin/clientes') ?>">Clientes</a>
    <a href="<?= url('admin/envios') ?>">Envíos</a>
    <a href="<?= url('admin/impuestos') ?>">Impuestos</a>
    <a href="<?= url('') ?>">Ver sitio</a>
    <a href="<?= url('admin/logout') ?>">Cerrar sesión</a>
  </aside>
  <main class="admin-main">
    <?php if ($msg = flash('success')): ?><div class="flash flash--success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="flash flash--error"><?= e($msg) ?></div><?php endif; ?>
    <?= $content ?>
  </main>
</div>
<?php else: ?>
<main class="admin-main">
  <?php if ($msg = flash('success')): ?><div class="flash flash--success"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($msg = flash('error')): ?><div class="flash flash--error"><?= e($msg) ?></div><?php endif; ?>
  <?= $content ?>
</main>
<?php endif; ?>
</body>
</html>
