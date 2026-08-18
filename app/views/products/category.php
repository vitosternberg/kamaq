<h1><?= e($category['name']) ?></h1>
<?php if (!empty($category['description'])): ?>
  <p><?= e($category['description']) ?></p>
<?php endif; ?>

<?php if (!empty($products)): ?>
  <div class="product-grid" style="margin-top:20px;">
    <?php foreach ($products as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p>No hay productos en esta categoría todavía.</p>
<?php endif; ?>
