<?php
$cover = $product['cover'] ?? null;
$img = $cover ? upload('products/' . $cover) : asset('img/placeholder.svg');
$cardPrice = (float) $product['price'];
$hasSale = !empty($product['sale_price']) && (float) $product['sale_price'] > 0;
if ($hasSale) {
    $cardPrice = (float) $product['sale_price'];
}
$discount = 0;
if ($hasSale && (float) $product['price'] > 0) {
    $discount = (int) round((1 - ((float) $product['sale_price'] / (float) $product['price'])) * 100);
}
$productUrl = url('producto/' . e($product['slug']));
?>
<div class="product-card">
  <a class="product-card__img" href="<?= $productUrl ?>">
    <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
    <?php if ($discount > 0): ?><span class="badge badge--discount">-<?= $discount ?>%</span><?php endif; ?>
    <?php if (!empty($product['is_bestseller'])): ?><span class="badge badge--bestseller">Super venta</span><?php elseif (!empty($product['is_featured'])): ?><span class="badge badge--featured">Destacado</span><?php endif; ?>
  </a>
  <div class="product-card__body">
    <h3 class="product-card__title"><a href="<?= $productUrl ?>"><?= e($product['name']) ?></a></h3>
    <div class="product-card__price">
      <?= money($cardPrice) ?>
      <?php if ($hasSale): ?><span class="product-card__old"><?= money($product['price']) ?></span><?php endif; ?>
    </div>
    <a class="btn btn--primary" href="<?= $productUrl ?>">Ver producto</a>
  </div>
</div>
