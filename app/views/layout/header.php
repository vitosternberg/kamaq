<?php $cartCount = \App\Core\Cart::count(); ?>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= url('') ?>"><?= e(config('app_name', 'KAMAQ')) ?></a>
    <nav class="main-nav">
      <a href="<?= url('') ?>">Inicio</a>
      <a href="<?= url('catalogo') ?>">Catálogo</a>
      <a href="<?= url('corporativo') ?>">Corporativo</a>
      <a href="<?= url('contacto') ?>">Contacto</a>
    </nav>
    <a class="cart-link" href="<?= url('carrito') ?>">Carrito (<?= $cartCount ?>)</a>
  </div>
</header>
