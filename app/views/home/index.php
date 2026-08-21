<?php $heroProduct = $featured[0] ?? null; ?>
<section class="hero">
  <div class="hero__content">
    <p class="hero__eyebrow">Regalos y mementos personalizados</p>
    <h1>Detalles únicos para cada ocasión</h1>
    <p class="hero__sub">Regalos corporativos, bautizos, baby shower, matrimonios y más. Hechos con cariño y listos para personalizar.</p>
    <div class="hero__actions">
      <a class="btn btn--primary" href="<?= url('catalogo') ?>">Ver catálogo</a>
      <a class="btn btn--outline" href="<?= url('corporativo') ?>">Regalos corporativos</a>
    </div>
  </div>
  <div class="hero__media">
    <img src="<?= e(($heroProduct && !empty($heroProduct['cover'])) ? upload('products/' . $heroProduct['cover']) : asset('img/placeholder.svg')) ?>" alt="<?= e($heroProduct['name'] ?? 'KAMAQ') ?>">
  </div>
</section>

<div class="trust-bar">
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Envío a todo Chile</span></div>
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Personalización incluida</span></div>
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Pago seguro</span></div>
</div>

<h2 class="section-title">Categorías</h2>
<div class="category-grid">
  <?php foreach ($categories as $cat): ?>
    <a class="category-card" href="<?= url('categoria/' . e($cat['slug'])) ?>"><?= e($cat['name']) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!empty($featured)): ?>
  <h2 class="section-title">Destacados</h2>
  <div class="product-grid">
    <?php foreach ($featured as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
