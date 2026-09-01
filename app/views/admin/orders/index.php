<div class="topbar">
  <h1>Pedidos</h1>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th>Nº</th><th>Cliente</th><th>Email</th><th>Total</th><th>Pago</th><th>Medio de pago</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><?= e($o['order_number']) ?></td>
        <td><?= e($o['customer_name']) ?></td>
        <td><?= e($o['customer_email']) ?></td>
        <td><?= money($o['total']) ?></td>
        <td><?= e($o['payment_status'] ?? 'pendiente') ?></td>
        <td><?= e(payment_method_label((string) ($o['payment_method'] ?? ''))) ?></td>
        <td><span class="badge badge--<?= $o['status'] === 'pendiente' ? 'off' : 'ok' ?>"><?= e($o['status']) ?></span></td>
        <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
        <td><a class="btn btn--outline btn--sm" href="<?= url('admin/pedidos/' . (int) $o['id']) ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($orders)): ?><p>No hay pedidos todavía.</p><?php endif; ?>
</div>
