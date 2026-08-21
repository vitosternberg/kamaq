<?php
$cartCount = \App\Core\Cart::count();
$customer = \App\Core\CustomerAuth::user();
$menuTree = \App\Models\Category::menuTree();

$renderMenu = function (array $nodes) use (&$renderMenu): void {
    if (!$nodes) {
        return;
    }
    echo '<ul>';
    foreach ($nodes as $node) {
        $hasChildren = !empty($node['children']);
        echo '<li' . ($hasChildren ? ' class="has-children"' : '') . '>';
        echo '<a href="' . url('categoria/' . e($node['slug'])) . '">' . e($node['name']) . '</a>';
        if ($hasChildren) {
            $renderMenu($node['children']);
        }
        echo '</li>';
    }
    echo '</ul>';
};
?>
<header class="site-header">
  <div class="container">
    <div class="header-main">
      <a class="brand" href="<?= url('') ?>"><?= e(config('app_name', 'KAMAQ')) ?></a>
      <form class="header-search" action="<?= url('buscar') ?>" method="get" role="search">
        <input type="search" name="q" placeholder="Buscar productos, regalos y más…" aria-label="Buscar" value="<?= e($_GET['q'] ?? '') ?>">
        <button type="submit" aria-label="Buscar">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
      </form>
      <?php if ($customer): ?>
        <a class="account-link" href="<?= url('cuenta/salir') ?>">Salir</a>
      <?php else: ?>
        <a class="account-link" href="<?= url('cuenta/ingresar') ?>">Ingresar</a>
      <?php endif; ?>
      <a class="cart-link" href="<?= url('carrito') ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span class="cart-link__label">Carrito</span>
        <span class="cart-link__count"><?= (int) $cartCount ?></span>
      </a>
    </div>
    <nav class="header-nav">
      <a href="<?= url('') ?>">Inicio</a>
      <div class="nav-item nav-item--services">
        <button type="button" class="nav-item__toggle">Servicios <span class="caret">▾</span></button>
        <div class="nav-dropdown">
          <?php $renderMenu($menuTree); ?>
        </div>
      </div>
      <a href="<?= url('catalogo') ?>">Catálogo</a>
      <a href="<?= url('corporativo') ?>">Corporativo</a>
      <a href="<?= url('proyectos') ?>">Proyectos</a>
      <a href="<?= url('contacto') ?>">Contacto</a>
    </nav>
  </div>
</header>
