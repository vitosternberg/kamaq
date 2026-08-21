<?php if (!empty($featured)): ?>
<section class="hero" data-hero>
  <div class="hero__viewport">
    <?php foreach ($featured as $i => $product): ?>
      <?php
      $slidePrice = (float) $product['price'];
      $slideHasSale = !empty($product['sale_price']) && (float) $product['sale_price'] > 0;
      if ($slideHasSale) {
          $slidePrice = (float) $product['sale_price'];
      }
      ?>
      <article class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide>
        <img class="hero-slide__bg" src="<?= e(!empty($product['cover']) ? upload('products/' . $product['cover']) : asset('img/placeholder.svg')) ?>" alt="" aria-hidden="true" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
        <img class="hero-slide__img" src="<?= e(!empty($product['cover']) ? upload('products/' . $product['cover']) : asset('img/placeholder.svg')) ?>" alt="<?= e($product['name']) ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
        <div class="hero-slide__content">
          <p class="hero-slide__tag">Destacado</p>
          <h2><?= e($product['name']) ?></h2>
          <?php if (!empty($product['short_description'])): ?>
            <p class="hero-slide__desc"><?= e($product['short_description']) ?></p>
          <?php endif; ?>
          <p class="hero-slide__price">
            <?= money($slidePrice) ?>
            <?php if ($slideHasSale): ?><span class="hero-slide__old"><?= money($product['price']) ?></span><?php endif; ?>
          </p>
          <a class="btn btn--primary" href="<?= url('producto/' . e($product['slug'])) ?>">Ver producto</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if (count($featured) > 1): ?>
    <button class="hero__nav hero__nav--prev" type="button" data-hero-prev aria-label="Anterior">‹</button>
    <button class="hero__nav hero__nav--next" type="button" data-hero-next aria-label="Siguiente">›</button>
    <div class="hero__dots">
      <?php foreach ($featured as $i => $product): ?>
        <button class="hero__dot<?= $i === 0 ? ' is-active' : '' ?>" type="button" data-hero-dot="<?= $i ?>" aria-label="Ir al destacado <?= $i + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<div class="trust-bar">
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Envío a todo Chile</span></div>
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Personalización incluida</span></div>
  <div class="trust-bar__item"><span class="trust-bar__icon">✓</span><span>Pago seguro</span></div>
</div>
