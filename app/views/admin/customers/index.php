<div class="topbar">
  <h1>Clientes</h1>
</div>

<div class="stats">
  <div class="stat"><div class="num"><?= (int) $stats['customers'] ?></div><div class="label">Clientes</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['orders'] ?></div><div class="label">Pedidos</div></div>
  <div class="stat"><div class="num"><?= money($stats['revenue']) ?></div><div class="label">Ingreso total</div></div>
  <div class="stat"><div class="num"><?= money($stats['avgTicket']) ?></div><div class="label">Ticket promedio</div></div>
</div>

<div class="card">
  <table class="data">
    <thead>
      <tr><th>Cliente</th><th>Email</th><th>Teléfono</th><th>Ciudad</th><th>Pedidos</th><th>Total</th><th>Ticket prom.</th><th>Última compra</th><th>Estado</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($customers as $c): $seg = \App\Models\Customer::segmentMeta($c['segment']); ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['email']) ?></td>
        <td><?= e($c['phone'] ?? '') ?></td>
        <td><?= e($c['city'] ?? '') ?></td>
        <td><?= (int) $c['order_count'] ?></td>
        <td><?= money($c['total_spent']) ?></td>
        <td><?= money($c['avg_ticket']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($c['last_order']))) ?></td>
        <td><span class="badge badge--<?= e($seg[1]) ?>"><?= e($seg[0]) ?></span></td>
        <td><a class="btn btn--outline btn--sm" href="<?= e(url('admin/clientes/' . rawurlencode($c['email']))) ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($customers)): ?><p>No hay clientes todavía.</p><?php endif; ?>
</div>
