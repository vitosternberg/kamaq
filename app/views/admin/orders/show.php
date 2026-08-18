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
      Subtotal: <?= money($order['subtotal']) ?><br>
      Envío: <?= money($order['shipping']) ?><br>
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
