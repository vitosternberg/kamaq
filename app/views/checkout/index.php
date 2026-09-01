<h1>Finalizar compra</h1>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:32px;">
  <form method="post" action="<?= url('checkout') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nombre completo</label>
      <input type="text" name="name" class="form-control" value="<?= e($customer['name']) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" class="form-control" value="<?= e($customer['email']) ?>" disabled>
    </div>
    <div class="form-group">
      <label>Teléfono</label>
      <input type="text" name="phone" class="form-control" value="<?= e($customer['phone'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Dirección de envío</label>
      <input type="text" name="address" class="form-control" value="<?= e($customer['address'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Ciudad</label>
      <input type="text" name="city" class="form-control" value="<?= e($customer['city'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Región</label>
      <input type="text" class="form-control" value="<?= e($customer['region'] ?? '') ?>" disabled>
    </div>

    <?php if (!empty($shippingOptions)): ?>
      <div class="form-group">
        <label>Método de envío</label>
        <div class="form-hint" style="margin-bottom:8px;">
          Peso del envío: <?= e(number_format($weight, 3, ',', '.')) ?> kg · Talla <?= e($tier) ?> · <?= e(\App\Models\Shipping::zoneLabel($zone)) ?>
        </div>
        <?php foreach ($shippingOptions as $i => $o): ?>
          <div>
            <label style="font-weight:400; display:flex; align-items:center; gap:8px;">
              <input type="radio" name="shipping_option" value="<?= e($o['key']) ?>" data-price="<?= (float) $o['price'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
              <?= e($o['name']) ?> — <?= (float) $o['price'] > 0 ? money($o['price']) : 'Gratis' ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label>Medio de pago</label>
      <?php foreach ($paymentMethods as $i => $pm): ?>
        <div>
          <label style="font-weight:400; display:flex; align-items:center; gap:8px;">
            <input type="radio" name="payment_method" value="<?= e($pm['key']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
            <?= e($pm['label']) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-group">
      <label>Tipo de documento</label>
      <div style="padding:10px 12px; border:1px solid var(--border); border-radius:var(--radius); background:#f7f7f7;">
        <strong><?= e(doc_type_label($doc['doc_type'])) ?></strong>
        <?php if ($doc['doc_type'] === 'factura'): ?>
          <div class="form-hint" style="margin-top:6px;">
            RUT: <?= e($doc['doc_rut']) ?><br>
            Razón social: <?= e($doc['doc_company']) ?><br>
            Giro: <?= e($doc['doc_giro']) ?>
          </div>
        <?php elseif (!empty($doc['doc_rut'])): ?>
          <div class="form-hint" style="margin-top:6px;">RUT: <?= e($doc['doc_rut']) ?></div>
        <?php endif; ?>
        <div class="form-hint" style="margin-top:6px;">Se define al crear tu cuenta y no se puede cambiar aquí.</div>
      </div>
    </div>

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
    <div style="display:flex; justify-content:space-between;"><span>Subtotal (neto)</span><span><?= money($subtotal) ?></span></div>
    <div style="display:flex; justify-content:space-between;"><span>Impuestos</span><span><?= money($tax) ?></span></div>
    <div style="display:flex; justify-content:space-between; font-weight:700;"><span>Total (con IVA)</span><span><?= money($subtotal + $tax) ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-top:8px;"><span>Envío</span><span id="shipping-total"><?= $shipping > 0 ? money($shipping) : 'Gratis' ?></span></div>
    <hr>
    <div style="display:flex; justify-content:space-between; font-weight:700; font-size:18px;"><span>Total final</span><span id="order-total"><?= money($subtotal + $tax + $shipping) ?></span></div>
  </div>
</div>

<script>
(function () {
  var radios = document.querySelectorAll('input[name="shipping_option"]');
  var shipEl = document.getElementById('shipping-total');
  var totalEl = document.getElementById('order-total');
  if (!radios.length || !shipEl || !totalEl) { return; }
  var subtotal = <?= (float) $subtotal ?>;
  var tax = <?= (float) $tax ?>;
  function fmt(n) { return '$' + Math.round(n).toLocaleString('es-CL'); }
  function refresh() {
    var el = document.querySelector('input[name="shipping_option"]:checked');
    var price = el ? parseFloat(el.getAttribute('data-price')) : 0;
    shipEl.textContent = price > 0 ? fmt(price) : 'Gratis';
    totalEl.textContent = fmt(subtotal + tax + price);
  }
  for (var i = 0; i < radios.length; i++) { radios[i].addEventListener('change', refresh); }
})();
</script>
