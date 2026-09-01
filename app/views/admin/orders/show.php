<div class="topbar">
  <h1>Pedido <?= e($order['order_number']) ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/pedidos') ?>">Volver</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
  <div class="card">
    <h2 style="margin-top:0;">Datos del cliente</h2>
    <p><strong><?= e($order['customer_name']) ?></strong></p>
    <p><?= e($order['customer_email']) ?></p>
    <?php if (!empty($order['customer_phone'])): ?><p><?= e($order['customer_phone']) ?></p><?php endif; ?>
    <?php if (!empty($order['address'])): ?><p><?= e($order['address']) ?>, <?= e($order['city']) ?> <?= e($order['region']) ?></p><?php endif; ?>
    <?php if (!empty($order['notes'])): ?><p><strong>Notas:</strong> <?= nl2br(e($order['notes'])) ?></p><?php endif; ?>
    <p><strong>Medio de pago:</strong> <?= e(payment_method_label((string) ($order['payment_method'] ?? ''))) ?></p>
    <p><strong>Tipo de documento:</strong> <?= e(doc_type_label((string) ($order['doc_type'] ?? 'boleta'))) ?></p>
    <?php if (($order['doc_type'] ?? '') === 'factura'): ?>
      <p><strong>RUT empresa:</strong> <?= e($order['doc_rut'] ?? '') ?></p>
      <p><strong>Razón social:</strong> <?= e($order['doc_company'] ?? '') ?></p>
      <p><strong>Giro:</strong> <?= e($order['doc_giro'] ?? '') ?></p>
    <?php elseif (!empty($order['doc_rut'])): ?>
      <p><strong>RUT:</strong> <?= e($order['doc_rut']) ?></p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Ítems</h2>
    <table class="data">
      <thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($order['items'] as $it): ?>
        <tr>
          <td><?= e($it['product_name']) ?></td>
          <td><?= (int) $it['quantity'] ?></td>
          <td><?= money($it['subtotal']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <p style="margin-top:12px;">
      Subtotal (neto): <?= money($order['subtotal']) ?><br>
      Impuestos: <?= money($order['tax'] ?? 0) ?><br>
      Envío: <?= money($order['shipping']) ?><?= !empty($order['shipping_method']) ? ' (' . e($order['shipping_method']) . ')' : '' ?><br>
      <strong>Total: <?= money($order['total']) ?></strong>
    </p>
  </div>
</div>

<div class="card">
  <h2 style="margin-top:0;">Actualizar estado</h2>
  <form method="post" action="<?= url('admin/pedidos/estado/' . (int) $order['id']) ?>" style="display:flex; gap:10px; align-items:center;">
    <?= csrf_field() ?>
    <select name="status" class="form-control" style="max-width:220px;">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn--primary" type="submit">Guardar</button>
  </form>
</div>

<div class="card">
  <h2 style="margin-top:0;">Documento tributario (DTE)</h2>
  <?php if (is_array($dte) && $dte): ?>
    <p>
      <strong>Tipo:</strong>
      <?= (int) ($dte['tipo'] ?? 0) === 33 ? 'Factura' : 'Boleta' ?>
      (<?= (int) ($dte['tipo'] ?? 0) ?>)
    </p>
    <p><strong>Estado:</strong> <?= e((string) ($dte['estado'] ?? '')) ?></p>
    <?php if (!empty($dte['folio'])): ?>
      <p><strong>Folio:</strong> <?= e((string) $dte['folio']) ?></p>
    <?php endif; ?>
    <?php if (!empty($dte['track_id'])): ?>
      <p><strong>Track ID:</strong> <?= e((string) $dte['track_id']) ?></p>
    <?php endif; ?>
    <?php if (!empty($dte['codigo'])): ?>
      <p><strong>Código temporal:</strong> <?= e((string) $dte['codigo']) ?></p>
    <?php endif; ?>
    <?php if (!empty($dte['glosa'])): ?>
      <p><strong>Glosa:</strong> <?= e((string) $dte['glosa']) ?></p>
    <?php endif; ?>
    <?php if (!empty($dte['pdf_url'])): ?>
      <p><a class="btn btn--outline" href="<?= e((string) $dte['pdf_url']) ?>" target="_blank" rel="noopener">Ver PDF</a></p>
    <?php endif; ?>
  <?php else: ?>
    <p>No hay documento tributario registrado para este pedido.</p>
  <?php endif; ?>

  <?php if (!is_array($dte) || ($dte['estado'] ?? '') !== 'emitido'): ?>
    <form method="post" action="<?= url('admin/pedidos/dte/' . (int) $order['id']) ?>" style="margin-top:12px;">
      <?= csrf_field() ?>
      <button class="btn btn--primary" type="submit">Reintentar emisión</button>
    </form>
  <?php endif; ?>
</div>
