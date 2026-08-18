<section class="hero">
  <h1>Regalos y mementos personalizados</h1>
  <p>Regalos corporativos, bautizos, baby shower, matrimonios, cumpleaños, cajas de vino y joyeros. Detalles únicos para cada ocasión.</p>
  <a class="btn btn--primary" href="<?= url('catalogo') ?>">Ver catálogo</a>
</section>

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
