<h1>Catálogo</h1>

<div class="catalog-layout">
  <aside class="catalog-sidebar">
    <form method="get" action="<?= url('catalogo') ?>">
      <div class="form-group">
        <label>Ordenar por</label>
        <select name="orden" class="form-control">
          <option value="recientes" <?= $orderKey === 'recientes' ? 'selected' : '' ?>>Recientes</option>
          <option value="precio-asc" <?= $orderKey === 'precio-asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
          <option value="precio-desc" <?= $orderKey === 'precio-desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
          <option value="nombre-asc" <?= $orderKey === 'nombre-asc' ? 'selected' : '' ?>>Nombre: A → Z</option>
          <option value="nombre-desc" <?= $orderKey === 'nombre-desc' ? 'selected' : '' ?>>Nombre: Z → A</option>
        </select>
      </div>
      <div class="form-group">
        <label>Categoría</label>
        <select name="categoria" class="form-control">
          <option value="0">Todas</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </aside>

  <div class="catalog-main">
    <?php if (!empty($products)): ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <nav style="margin-top:24px; text-align:center;">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="btn <?= $i === $page ? 'btn--primary' : 'btn--outline' ?>" href="<?= url('catalogo') ?>?page=<?= $i ?>&orden=<?= e($orderKey) ?>&categoria=<?= (int) $categoryId ?>"><?= $i ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <p>No hay productos para los filtros seleccionados.</p>
    <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll('.catalog-sidebar select').forEach(function (sel) {
  sel.addEventListener('change', function () { this.form.submit(); });
});
</script>
