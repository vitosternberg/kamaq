<?php
$price = (float) $product['price'];
$hasSale = !empty($product['sale_price']) && (float) $product['sale_price'] > 0;
if ($hasSale) {
    $price = (float) $product['sale_price'];
}
$taxId = ($product['tax_id'] ?? null) !== null ? (int) $product['tax_id'] : null;
$price = gross_price($price, $taxId);
$oldPrice = $hasSale ? gross_price((float) $product['price'], $taxId) : 0.0;
$mainImage = !empty($images) ? $images[0]['filename'] : null;
$trackStock = (int) ($product['track_stock'] ?? 1);
?>
<div class="product-detail">
  <div class="product-gallery">
    <div class="product-gallery__main">
      <img src="<?= e($mainImage ? upload('products/' . $mainImage) : asset('img/placeholder.svg')) ?>" alt="<?= e($product['name']) ?>">
    </div>
    <?php if (count($images) > 1): ?>
      <div class="product-gallery__thumbs">
        <?php foreach ($images as $i => $img): ?>
          <img src="<?= e(upload('products/' . $img['filename'])) ?>" alt="<?= e($product['name']) ?>" class="<?= $i === 0 ? 'active' : '' ?>">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <?php if ($category): ?>
      <p class="product-detail__meta"><a href="<?= url('categoria/' . e($category['slug'])) ?>"><?= e($category['name']) ?></a></p>
    <?php endif; ?>
    <h1><?= e($product['name']) ?></h1>
    <div class="product-detail__price">
      <?= money($price) ?>
      <span style="font-size:15px; color:var(--muted);">/ <?= e(product_unit_label($product['unit'] ?? 'unidad')) ?></span>
      <?php if ($hasSale): ?><span class="product-card__old" style="font-size:18px;"><?= money($oldPrice) ?></span><?php endif; ?>
    </div>
    <?php if (!empty($product['short_description'])): ?>
      <p><?= e($product['short_description']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= url('carrito/agregar') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
      <div class="form-group" style="max-width:120px;">
        <label>Cantidad</label>
        <input type="number" name="quantity" class="form-control" value="1" min="1" <?= $trackStock ? 'max="' . (int) $product['stock'] . '"' : '' ?>>
      </div>
      <?php if ($trackStock && (int) $product['stock'] <= 0): ?>
        <p style="color:var(--muted);">Sin stock disponible.</p>
      <?php elseif (!$trackStock): ?>
        <p style="color:var(--muted);">Bajo pedido.</p>
      <?php endif; ?>
      <button type="submit" class="btn btn--primary" <?= ($trackStock && (int) $product['stock'] <= 0) ? 'disabled' : '' ?>>Agregar al carrito</button>
    </form>

    <?php if (!empty($product['description'])): ?>
      <div style="margin-top:20px;"><?= nl2br(e($product['description'])) ?></div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($related)): ?>
  <h2 class="section-title" style="margin-top:40px;">Productos relacionados</h2>
  <div class="product-grid">
    <?php foreach ($related as $product): ?>
      <?php include BASE_PATH . '/app/views/partials/product_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
