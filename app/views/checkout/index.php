<h1>Finalizar compra</h1>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:32px;">
  <form method="post" action="<?= url('checkout') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nombre completo</label>
      <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="form-group">
      <label>Teléfono</label>
      <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
    </div>
    <div class="form-group">
      <label>Dirección de envío</label>
      <input type="text" name="address" class="form-control" value="<?= e(old('address')) ?>">
    </div>
    <div class="form-group">
      <label>Ciudad</label>
      <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
    </div>
    <div class="form-group">
      <label>Región</label>
      <input type="text" name="region" class="form-control" value="<?= e(old('region')) ?>">
    </div>

    <?php if (!empty($shippingMethods)): ?>
      <div class="form-group">
        <label>Método de envío</label>
        <?php foreach ($shippingMethods as $i => $m): ?>
          <div>
            <label style="font-weight:400; display:flex; align-items:center; gap:8px;">
              <input type="radio" name="shipping_method_id" value="<?= (int) $m['id'] ?>" data-price="<?= (float) $m['price'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
              <?= e($m['name']) ?> — <?= (float) $m['price'] > 0 ? money($m['price']) : 'Gratis' ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label>Notas del pedido</label>
      <textarea name="notes" class="form-control"><?= e(old('notes')) ?></textarea>
    </div>
    <button class="btn btn--primary" type="submit">Enviar pedido</button>
  </form>

  <div class="card" style="border:1px solid var(--border); border-radius:var(--radius); padding:20px; background:#fff;">
    <h2 style="margin-top:0;">Resumen</h2>
    <?php foreach ($items as $item): ?>
      <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
        <span><?= e($item['product']['name']) ?> × <?= (int) $item['quantity'] ?></span>
        <span><?= money($item['subtotal']) ?></span>
      </div>
    <?php endforeach; ?>
    <hr>
    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
    <div style="display:flex; justify-content:space-between;"><span>Envío</span><span id="shipping-total"><?= $shipping > 0 ? money($shipping) : 'Gratis' ?></span></div>
    <hr>
    <div style="display:flex; justify-content:space-between; font-weight:700; font-size:18px;"><span>Total</span><span id="order-total"><?= money($subtotal + $shipping) ?></span></div>
  </div>
</div>

<script>
(function () {
  var radios = document.querySelectorAll('input[name="shipping_method_id"]');
  var shipEl = document.getElementById('shipping-total');
  var totalEl = document.getElementById('order-total');
  if (!radios.length || !shipEl || !totalEl) { return; }
  var subtotal = <?= (float) $subtotal ?>;
  function fmt(n) { return '$' + Math.round(n).toLocaleString('es-CL'); }
  function refresh() {
    var el = document.querySelector('input[name="shipping_method_id"]:checked');
    var price = el ? parseFloat(el.getAttribute('data-price')) : 0;
    shipEl.textContent = price > 0 ? fmt(price) : 'Gratis';
    totalEl.textContent = fmt(subtotal + price);
  }
  for (var i = 0; i < radios.length; i++) { radios[i].addEventListener('change', refresh); }
})();
</script>
