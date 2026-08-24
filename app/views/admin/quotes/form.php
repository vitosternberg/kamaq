<?php
$isEdit = $quote !== null;
$items = $isEdit ? $quote['items'] : [];
$taxRate = $isEdit ? (float) $quote['tax_rate'] : 19.0;
$productsMap = [];
foreach ($products as $p) {
    $productsMap[(int) $p['id']] = $p;
}
$optionsHtml = '<option value="">— Selecciona producto —</option>';
foreach ($products as $p) {
    $optionsHtml .= '<option value="' . (int) $p['id'] . '" data-price="' . e((string) $p['price']) . '">' . e($p['name']) . ' — ' . money($p['price']) . ' neto</option>';
}
?>
<div class="topbar">
  <h1><?= $isEdit ? 'Editar cotización' : 'Nueva cotización' ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/cotizaciones') ?>">Volver</a>
</div>

<form method="post" action="<?= $isEdit ? url('admin/cotizaciones/actualizar/' . (int) $quote['id']) : url('admin/cotizaciones/guardar') ?>" id="quote-form">
  <?= csrf_field() ?>

  <div class="card">
    <h2 style="margin-top:0;">Cliente</h2>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
      <div class="form-group">
        <label>RUT</label>
        <div style="display:flex; gap:8px;">
          <input type="text" name="customer_rut" id="customer-rut" class="form-control" style="flex:1;" value="<?= e(old('customer_rut', $quote['customer_rut'] ?? '')) ?>">
          <button type="button" class="btn btn--outline" id="rut-lookup">Buscar</button>
        </div>
        <div class="form-hint" id="rut-hint" style="display:none;"></div>
      </div>
      <div class="form-group">
        <label id="company-label">Razón social / Nombre *</label>
        <input type="text" name="customer_company" class="form-control" value="<?= e(old('customer_company', $quote['customer_company'] ?? '')) ?>" required>
      </div>
      <div class="form-group">
        <label>Dirección</label>
        <input type="text" name="customer_address" class="form-control" value="<?= e(old('customer_address', $quote['customer_address'] ?? '')) ?>">
      </div>
      <div class="form-group">
        <label>Atención (persona de contacto)</label>
        <input type="text" name="contact_person" class="form-control" value="<?= e(old('contact_person', $quote['contact_person'] ?? '')) ?>">
      </div>
      <div class="form-group">
        <label>Correo</label>
        <input type="email" name="customer_email" class="form-control" value="<?= e(old('customer_email', $quote['customer_email'] ?? '')) ?>">
      </div>
      <div class="form-group">
        <label>Teléfono</label>
        <input type="tel" name="customer_phone" class="form-control" value="<?= e(old('customer_phone', $quote['customer_phone'] ?? '')) ?>">
      </div>
      <div class="form-group">
        <label>IVA (%)</label>
        <input type="number" step="0.01" name="tax_rate" id="tax-rate" class="form-control" value="<?= e(old('tax_rate', (string) $taxRate)) ?>">
      </div>
    </div>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Productos</h2>
    <table class="data" id="quote-lines">
      <thead>
        <tr><th style="width:40%;">Producto</th><th>Cant.</th><th>P. unitario (neto)</th><th style="width:24%;">Indicaciones</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr class="quote-line">
          <td>
            <select name="items[product_id][]" class="form-control line-product">
              <option value="">— Selecciona producto —</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>" data-price="<?= e((string) $p['price']) ?>" <?= ((int) $item['product_id'] === (int) $p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?> — <?= money($p['price']) ?> neto</option>
              <?php endforeach; ?>
              <?php if ($item['product_id'] && !isset($productsMap[(int) $item['product_id']])): ?>
                <option value="<?= (int) $item['product_id'] ?>" data-price="<?= e((string) $item['unit_price']) ?>" selected><?= e($item['product_name']) ?> (producto eliminado)</option>
              <?php endif; ?>
            </select>
          </td>
          <td><input type="number" name="items[quantity][]" min="1" value="<?= (int) $item['quantity'] ?>" class="form-control line-qty"></td>
          <td><input type="number" step="0.01" min="0" name="items[unit_price][]" value="<?= e((string) $item['unit_price']) ?>" class="form-control line-price"></td>
          <td><input type="text" name="items[notes][]" value="<?= e($item['notes'] ?? '') ?>" class="form-control line-notes" placeholder="Indicaciones extra"></td>
          <td><button type="button" class="btn btn--danger btn--sm line-remove">×</button></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?>
        <tr class="quote-line">
          <td><select name="items[product_id][]" class="form-control line-product"><?= $optionsHtml ?></select></td>
          <td><input type="number" name="items[quantity][]" min="1" value="1" class="form-control line-qty"></td>
          <td><input type="number" step="0.01" min="0" name="items[unit_price][]" value="" class="form-control line-price"></td>
          <td><input type="text" name="items[notes][]" value="" class="form-control line-notes" placeholder="Indicaciones extra"></td>
          <td><button type="button" class="btn btn--danger btn--sm line-remove">×</button></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <button type="button" class="btn btn--outline" id="add-line" style="margin-top:10px;">+ Agregar línea</button>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Indicaciones generales</h2>
    <textarea name="notes" class="form-control" placeholder="Instrucciones adicionales que no están en la descripción de los productos (colores, plazos, marca, etc.)"><?= e(old('notes', $quote['notes'] ?? '')) ?></textarea>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Totales</h2>
    <p>Subtotal (neto): <strong id="tot-subtotal">$0</strong><br>
    IVA (<span id="tot-rate"><?= e((string) $taxRate) ?></span>%): <strong id="tot-tax">$0</strong><br>
    Total (bruto): <strong id="tot-total">$0</strong></p>
    <div style="display:flex; gap:10px; margin-top:14px;">
      <button type="submit" name="action" value="guardar" class="btn btn--outline">Guardar borrador</button>
      <button type="submit" name="action" value="enviar" class="btn btn--primary">Guardar y enviar</button>
    </div>
  </div>
</form>

<template id="line-template">
  <tr class="quote-line">
    <td><select name="items[product_id][]" class="form-control line-product"><?= $optionsHtml ?></select></td>
    <td><input type="number" name="items[quantity][]" min="1" value="1" class="form-control line-qty"></td>
    <td><input type="number" step="0.01" min="0" name="items[unit_price][]" value="" class="form-control line-price"></td>
    <td><input type="text" name="items[notes][]" value="" class="form-control line-notes" placeholder="Indicaciones extra"></td>
    <td><button type="button" class="btn btn--danger btn--sm line-remove">×</button></td>
  </tr>
</template>

<script>
(function () {
  var tbody = document.querySelector('#quote-lines tbody');
  var template = document.getElementById('line-template');
  var addBtn = document.getElementById('add-line');
  var taxRateEl = document.getElementById('tax-rate');

  function format(n) {
    return '$' + Math.round(n).toLocaleString('es-CL');
  }

  function recalc() {
    var subtotal = 0;
    tbody.querySelectorAll('tr.quote-line').forEach(function (row) {
      var qty = parseFloat(row.querySelector('.line-qty').value) || 0;
      var price = parseFloat(row.querySelector('.line-price').value) || 0;
      subtotal += qty * price;
    });
    var rate = parseFloat(taxRateEl.value) || 0;
    var tax = subtotal * rate / 100;
    var total = subtotal + tax;
    document.getElementById('tot-subtotal').textContent = format(subtotal);
    document.getElementById('tot-tax').textContent = format(tax);
    document.getElementById('tot-total').textContent = format(total);
    document.getElementById('tot-rate').textContent = rate;
  }

  function bindRow(row) {
    row.querySelector('.line-product').addEventListener('change', function () {
      var opt = this.options[this.selectedIndex];
      if (opt && opt.dataset.price !== undefined && opt.dataset.price !== '') {
        row.querySelector('.line-price').value = opt.dataset.price;
      }
      recalc();
    });
    row.querySelector('.line-remove').addEventListener('click', function () {
      row.remove();
      recalc();
    });
    row.querySelector('.line-qty').addEventListener('input', recalc);
    row.querySelector('.line-price').addEventListener('input', recalc);
  }

  tbody.querySelectorAll('tr.quote-line').forEach(bindRow);

  if (addBtn) {
    addBtn.addEventListener('click', function () {
      var row = template.content.firstElementChild.cloneNode(true);
      tbody.appendChild(row);
      bindRow(row);
      recalc();
    });
  }

  taxRateEl.addEventListener('input', recalc);
  recalc();
})();
</script>

<script>
(function () {
  var rutInput = document.getElementById('customer-rut');
  var lookupBtn = document.getElementById('rut-lookup');
  var hint = document.getElementById('rut-hint');
  if (!rutInput || !lookupBtn) { return; }

  function set(name, value) {
    var el = document.querySelector('[name="' + name + '"]');
    if (el && value !== undefined && value !== null && value !== '') { el.value = value; }
  }

  lookupBtn.addEventListener('click', function () {
    var rut = rutInput.value.trim();
    if (!rut) { return; }
    lookupBtn.disabled = true;
    hint.style.display = 'none';
    fetch('<?= url('admin/cotizaciones/cliente') ?>?rut=' + encodeURIComponent(rut), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.found) {
          hint.textContent = 'No se encontró un cliente registrado con ese RUT.';
          hint.style.display = '';
          return;
        }
        set('customer_company', data.company);
        set('customer_address', data.address);
        set('customer_email', data.email);
        set('customer_phone', data.phone);
        set('contact_person', data.contact_person);
        var label = document.getElementById('company-label');
        if (label) { label.textContent = data.type === 'empresa' ? 'Razón social *' : 'Nombre *'; }
        hint.textContent = 'Datos cargados (' + (data.type === 'empresa' ? 'empresa' : 'persona natural') + '). Puedes editarlos.';
        hint.style.display = '';
      })
      .catch(function () {
        hint.textContent = 'Error al buscar el RUT.';
        hint.style.display = '';
      })
      .finally(function () {
        lookupBtn.disabled = false;
      });
  });
})();
</script>
