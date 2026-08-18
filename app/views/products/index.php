<h1>Catálogo</h1>

<div class="category-grid">
  <a class="category-card" href="<?= url('catalogo') ?>">Todos</a>
  <?php foreach ($categories as $cat): ?>
    <a class="category-card" href="<?= url('categoria/' . e($cat['slug'])) ?>"><?= e($cat['name']) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!empty($products)): ?>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav style="margin-top:24px; text-align:center;">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="btn <?= $i === $page ? 'btn--primary' : 'btn--outline' ?>" href="<?= url('catalogo') ?>?page=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
<?php else: ?>
  <p>No hay productos todavía.</p>
<?php endif; ?>
