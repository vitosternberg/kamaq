<?php
$cover = $product['cover'] ?? null;
$img = $cover ? upload('products/' . $cover) : asset('img/placeholder.svg');
$cardPrice = (float) $product['price'];
$hasSale = !empty($product['sale_price']) && (float) $product['sale_price'] > 0;
if ($hasSale) {
    $cardPrice = (float) $product['sale_price'];
}
?>
<div class="product-card">
  <a class="product-card__img" href="<?= url('producto/' . e($product['slug'])) ?>">
    <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
  </a>
  <div class="product-card__body">
    <h3 class="product-card__title"><a href="<?= url('producto/' . e($product['slug'])) ?>"><?= e($product['name']) ?></a></h3>
    <div class="product-card__price">
      <?= money($cardPrice) ?>
      <?php if ($hasSale): ?><span class="product-card__old"><?= money($product['price']) ?></span><?php endif; ?>
    </div>
    <a class="btn btn--primary" href="<?= url('producto/' . e($product['slug'])) ?>">Ver producto</a>
  </div>
</div>
