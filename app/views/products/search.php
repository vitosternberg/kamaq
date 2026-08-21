<h1>Búsqueda</h1>

<?php if ($q !== ''): ?>
  <p class="search-summary"><?= count($products) ?> resultado<?= count($products) === 1 ? '' : 's' ?> para “<?= e($q) ?>”.</p>
<?php endif; ?>

<?php if (!empty($products)): ?>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="search-empty">No encontramos productos<?= $q !== '' ? ' para “' . e($q) . '”' : '' ?>. Intenta con otra palabra.</p>
<?php endif; ?>
