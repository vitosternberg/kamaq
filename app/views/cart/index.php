<h1>Carrito</h1>

<?php if (!empty($items)): ?>
  <table class="cart-table">
    <thead>
      <tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): $p = $item['product']; $cover = $p['cover'] ?? null; ?>
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:12px;">
            <img class="cart-thumb" src="<?= e($cover ? upload('products/' . $cover) : asset('img/placeholder.svg')) ?>" alt="<?= e($p['name']) ?>">
            <a href="<?= url('producto/' . e($p['slug'])) ?>"><?= e($p['name']) ?></a>
          </div>
        </td>
        <td><?= money($item['price']) ?> <span style="color:var(--muted); font-size:13px;">neto</span></td>
        <td>
          <form method="post" action="<?= url('carrito/actualizar') ?>" style="display:flex; gap:6px;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) $p['stock'] ?>" class="qty-input">
          </form>
        </td>
        <td><?= money($item['subtotal']) ?></td>
        <td>
          <form method="post" action="<?= url('carrito/quitar') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn--danger btn--sm" type="submit">Quitar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div style="text-align:right; margin-top:20px; max-width:360px; margin-left:auto;">
    <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>Subtotal (neto)</span><span><?= money($subtotal) ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-bottom:8px;"><span>Impuestos</span><span><?= money($tax) ?></span></div>
    <div style="display:flex; justify-content:space-between; font-weight:700; font-size:20px;"><span>Total</span><span><?= money($subtotal + $tax) ?></span></div>
    <a class="btn btn--primary" href="<?= url('checkout') ?>" style="margin-top:16px; width:100%;">Finalizar compra</a>
  </div>
<?php else: ?>
  <p>Tu carrito está vacío. <a href="<?= url('catalogo') ?>">Ver catálogo</a></p>
<?php endif; ?>

<?php if (!empty($items)): ?>
<script>
(function () {
  document.querySelectorAll('.qty-input').forEach(function (input) {
    input.addEventListener('change', function () {
      this.closest('form').submit();
    });
  });
})();
</script>
<?php endif; ?>
