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
    <form class="header-search" action="<?= url('buscar') ?>" method="get" role="search">
      <input type="search" name="q" placeholder="Buscar regalos…" aria-label="Buscar" value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit" aria-label="Buscar">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>
    </form>
    <a class="cart-link" href="<?= url('carrito') ?>">Carrito (<?= $cartCount ?>)</a>
  </div>
</header>
