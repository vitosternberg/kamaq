<h1><?= e($category['name']) ?></h1>
<?php if (!empty($category['description'])): ?>
  <p><?= e($category['description']) ?></p>
<?php endif; ?>

<?php if (!empty($children)): ?>
  <h2 class="section-title" style="margin-top:20px;">Subcategorías</h2>
  <div class="category-grid">
    <?php foreach ($children as $child): ?>
      <a class="category-card" href="<?= url('categoria/' . e($child['slug'])) ?>"><?= e($child['name']) ?></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($products)): ?>
  <h2 class="section-title" style="margin-top:20px;">Productos</h2>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php elseif (empty($children)): ?>
  <p>No hay productos en esta categoría todavía.</p>
<?php endif; ?>
