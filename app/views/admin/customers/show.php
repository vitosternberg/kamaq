<?php $seg = \App\Models\Customer::segmentMeta($customer['segment']); ?>
<div class="topbar">
  <h1><?= e($customer['name']) ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/clientes') ?>">Volver</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
  <div class="card">
    <h2 style="margin-top:0;">Datos del cliente</h2>
    <p><strong><?= e($customer['name']) ?></strong> <span class="badge badge--<?= e($seg[1]) ?>"><?= e($seg[0]) ?></span></p>
    <p><?= e($customer['email']) ?></p>
    <?php if (!empty($customer['phone'])): ?><p><?= e($customer['phone']) ?></p><?php endif; ?>
    <?php if (!empty($customer['city'])): ?>
      <p><?= e($customer['city']) ?><?= !empty($customer['region']) ? ', ' . e($customer['region']) : '' ?></p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2 style="margin-top:0;">Resumen de compras</h2>
    <p>Pedidos: <strong><?= (int) $customer['order_count'] ?></strong></p>
    <p>Total comprado: <strong><?= money($customer['total_spent']) ?></strong></p>
    <p>Ticket promedio: <strong><?= money($customer['avg_ticket']) ?></strong></p>
    <p>Primera compra: <?= e(date('d/m/Y', strtotime($customer['first_order']))) ?></p>
    <p>Última compra: <?= e(date('d/m/Y', strtotime($customer['last_order']))) ?></p>
  </div>
</div>

<div class="card">
  <h2 style="margin-top:0;">Historial de compras</h2>
  <?php if (!empty($orders)): ?>
    <table class="data">
      <thead><tr><th>Nº</th><th>Fecha</th><th>Total</th><th>Pago</th><th>Estado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= e($o['order_number']) ?></td>
          <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
          <td><?= money($o['total']) ?></td>
          <td><?= e($o['payment_status'] ?? 'pendiente') ?></td>
          <td><span class="badge badge--<?= $o['status'] === 'pendiente' ? 'off' : 'ok' ?>"><?= e($o['status']) ?></span></td>
          <td><a class="btn btn--outline btn--sm" href="<?= url('admin/pedidos/' . (int) $o['id']) ?>">Ver</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Sin pedidos.</p>
  <?php endif; ?>
</div>
