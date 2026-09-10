<?php
$activeNav = $activeNav ?? 'dashboard';
?>
<nav class="account-nav" aria-label="Menú de cuenta">
  <a class="account-nav__link<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>" href="<?= url('cuenta') ?>">Resumen</a>
  <a class="account-nav__link<?= $activeNav === 'orders' ? ' is-active' : '' ?>" href="<?= url('cuenta/pedidos') ?>">Mis pedidos</a>
  <a class="account-nav__link" href="<?= url('cuenta/salir') ?>">Salir</a>
</nav>
