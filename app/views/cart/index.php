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
        <td><?= money($item['price']) ?></td>
        <td>
          <form method="post" action="<?= url('carrito/actualizar') ?>" style="display:flex; gap:6px;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" class="qty-input">
            <button class="btn btn--outline btn--sm" type="submit">Actualizar</button>
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

  <div style="text-align:right; margin-top:20px;">
    <p style="font-size:20px; font-weight:700;">Subtotal: <?= money($subtotal) ?></p>
    <a class="btn btn--primary" href="<?= url('checkout') ?>">Finalizar compra</a>
  </div>
<?php else: ?>
  <p>Tu carrito está vacío. <a href="<?= url('catalogo') ?>">Ver catálogo</a></p>
<?php endif; ?>
