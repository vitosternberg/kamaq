<style>
@media print {
  .admin-sidebar, .topbar, .no-print { display: none !important; }
  .admin-main { padding: 0 !important; }
  .card { border: none !important; box-shadow: none !important; }
}
</style>

<div class="topbar no-print">
  <h1>Cotización <?= e($quote['quote_number']) ?></h1>
  <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <a class="btn btn--outline" href="<?= url('admin/cotizaciones') ?>">Volver</a>
    <a class="btn btn--outline" href="<?= url('admin/cotizaciones/editar/' . (int) $quote['id']) ?>">Editar</a>
    <a class="btn btn--outline" href="<?= url('admin/cotizaciones/pdf/' . (int) $quote['id']) ?>">Descargar PDF</a>
    <button class="btn btn--outline" onclick="window.print()">Imprimir</button>
    <form method="post" action="<?= url('admin/cotizaciones/enviar/' . (int) $quote['id']) ?>" style="margin:0;">
      <?= csrf_field() ?>
      <button class="btn btn--primary" type="submit">Enviar por correo</button>
    </form>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
  <div class="card">
    <h2 style="margin-top:0;">Cliente</h2>
    <p><strong><?= e($quote['customer_company']) ?></strong></p>
    <?php if (!empty($quote['customer_rut'])): ?><p>RUT: <?= e($quote['customer_rut']) ?></p><?php endif; ?>
    <?php if (!empty($quote['contact_person'])): ?><p>Atención: <?= e($quote['contact_person']) ?></p><?php endif; ?>
    <?php if (!empty($quote['customer_address'])): ?><p><?= e($quote['customer_address']) ?></p><?php endif; ?>
    <?php if (!empty($quote['customer_email'])): ?><p><?= e($quote['customer_email']) ?></p><?php endif; ?>
    <?php if (!empty($quote['customer_phone'])): ?><p><?= e($quote['customer_phone']) ?></p><?php endif; ?>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Resumen</h2>
    <p>Nº: <strong><?= e($quote['quote_number']) ?></strong></p>
    <p>Fecha: <?= e(date('d/m/Y', strtotime($quote['created_at']))) ?></p>
    <p>Estado: <?= quote_status_badge($quote['status']) ?></p>
  </div>
</div>

<div class="card">
  <h2 style="margin-top:0;">Detalle</h2>
  <table class="data">
    <thead><tr><th>Producto</th><th>Cant.</th><th>P. unitario</th><th>Total</th><th>Indicaciones</th></tr></thead>
    <tbody>
    <?php foreach ($quote['items'] as $it): ?>
      <tr>
        <td><?= e($it['product_name']) ?></td>
        <td><?= (int) $it['quantity'] ?></td>
        <td><?= money($it['unit_price']) ?></td>
        <td><?= money($it['line_total']) ?></td>
        <td><?= nl2br(e($it['notes'] ?? '')) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p style="margin-top:12px;">
    Subtotal (neto): <?= money($quote['subtotal']) ?><br>
    IVA (<?= e(format_tax_rate($quote['tax_rate'])) ?>%): <?= money($quote['tax']) ?><br>
    <strong>Total (bruto): <?= money($quote['total']) ?></strong>
  </p>
</div>

<?php if (!empty($quote['notes'])): ?>
<div class="card">
  <h2 style="margin-top:0;">Indicaciones generales</h2>
  <p><?= nl2br(e($quote['notes'])) ?></p>
</div>
<?php endif; ?>

<div class="card no-print">
  <h2 style="margin-top:0;">Actualizar estado</h2>
  <form method="post" action="<?= url('admin/cotizaciones/estado/' . (int) $quote['id']) ?>" style="display:flex; gap:10px; align-items:center;">
    <?= csrf_field() ?>
    <select name="status" class="form-control" style="max-width:220px;">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= e($s) ?>" <?= $quote['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn--primary" type="submit">Guardar</button>
  </form>
</div>

<div class="card no-print">
  <form method="post" action="<?= url('admin/cotizaciones/eliminar/' . (int) $quote['id']) ?>" onsubmit="return confirm('¿Eliminar esta cotización?');">
    <?= csrf_field() ?>
    <button class="btn btn--danger" type="submit">Eliminar cotización</button>
  </form>
</div>
