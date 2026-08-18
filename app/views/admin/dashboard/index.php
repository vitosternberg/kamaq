<h1>Panel</h1>

<div class="stats">
  <div class="stat"><div class="num"><?= (int) $stats['products'] ?></div><div class="label">Productos</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['categories'] ?></div><div class="label">Categorías</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['orders'] ?></div><div class="label">Pedidos</div></div>
</div>

<h2>Últimos pedidos</h2>
<div class="card">
  <?php if (!empty($recentOrders)): ?>
    <table class="data">
      <thead><tr><th>Nº</th><th>Cliente</th><th>Total</th><th>Estado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td><?= e($o['order_number']) ?></td>
          <td><?= e($o['customer_name']) ?></td>
          <td><?= money($o['total']) ?></td>
          <td><span class="badge badge--<?= $o['status'] === 'pendiente' ? 'off' : 'ok' ?>"><?= e($o['status']) ?></span></td>
          <td><a class="btn btn--outline btn--sm" href="<?= url('admin/pedidos/' . (int) $o['id']) ?>">Ver</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay pedidos todavía.</p>
  <?php endif; ?>
</div>
