<h1>Mi cuenta</h1>
<p class="account-greeting">Hola, <strong><?= e($customer['name']) ?></strong></p>

<div class="account-layout">
  <?php include BASE_PATH . '/app/views/account/_nav.php'; ?>

  <div class="account-content">
    <div class="account-stats">
      <div class="account-stat">
        <span class="account-stat__value"><?= (int) $stats['order_count'] ?></span>
        <span class="account-stat__label">Pedidos</span>
      </div>
      <div class="account-stat">
        <span class="account-stat__value"><?= money($stats['total_spent']) ?></span>
        <span class="account-stat__label">Total comprado</span>
      </div>
      <div class="account-stat">
        <span class="account-stat__value"><?= money($stats['avg_ticket']) ?></span>
        <span class="account-stat__label">Ticket promedio</span>
      </div>
      <?php if (!empty($stats['last_order'])): ?>
      <div class="account-stat">
        <span class="account-stat__value"><?= e(date('d/m/Y', strtotime($stats['last_order']))) ?></span>
        <span class="account-stat__label">Última compra</span>
      </div>
      <?php endif; ?>
    </div>

    <div class="account-grid">
      <section class="account-card">
        <h2>Productos más comprados</h2>
        <?php if (!empty($topProducts)): ?>
          <div class="account-chart-wrap">
            <canvas id="top-products-chart" width="280" height="280" aria-label="Gráfico de productos más comprados"></canvas>
          </div>
          <ul class="account-product-list">
            <?php foreach ($topProducts as $p): ?>
              <li>
                <span><?= e($p['product_name']) ?></span>
                <strong><?= (int) $p['total_qty'] ?> uds.</strong>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="account-empty">Aún no tienes compras registradas.</p>
        <?php endif; ?>
      </section>

      <section class="account-card">
        <div class="account-card__head">
          <h2>Datos de contacto</h2>
        </div>
        <p><?= e($customer['email']) ?></p>
        <?php if (!empty($customer['phone'])): ?><p><?= e($customer['phone']) ?></p><?php endif; ?>
        <?php if (!empty($customer['address'])): ?>
          <p><?= e($customer['address']) ?><?= !empty($customer['city']) ? ', ' . e($customer['city']) : '' ?><?= !empty($customer['region']) ? ' (' . e($customer['region']) . ')' : '' ?></p>
        <?php endif; ?>
        <?php if (!empty($customer['rut'])): ?><p>RUT: <?= e($customer['rut']) ?></p><?php endif; ?>
      </section>
    </div>

    <section class="account-card">
      <div class="account-card__head">
        <h2>Pedidos recientes</h2>
        <?php if (!empty($orders)): ?>
          <a class="btn btn--outline btn--sm" href="<?= url('cuenta/pedidos') ?>">Ver todos</a>
        <?php endif; ?>
      </div>
      <?php if (!empty($orders)): ?>
        <table class="account-table">
          <thead>
            <tr><th>Nº pedido</th><th>Fecha</th><th>Total</th><th>Estado</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
                <td><?= money((float) $o['total']) ?></td>
                <td><span class="account-badge account-badge--<?= e($o['status']) ?>"><?= e(order_status_label((string) $o['status'])) ?></span></td>
                <td><a class="btn btn--outline btn--sm" href="<?= url('cuenta/pedidos/' . (int) $o['id']) ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="account-empty">Todavía no has realizado pedidos. <a href="<?= url('catalogo') ?>">Explora el catálogo</a></p>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php if (!empty($topProducts)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  var labels = <?= json_encode(array_map(fn($p) => $p['product_name'], $topProducts), JSON_UNESCAPED_UNICODE) ?>;
  var data = <?= json_encode(array_map(fn($p) => (int) $p['total_qty'], $topProducts)) ?>;
  var colors = ['#2e7d57', '#4caf82', '#81c784', '#a5d6a7', '#388e3c', '#66bb6a', '#1b5e20', '#c8e6c9'];
  var ctx = document.getElementById('top-products-chart');
  if (!ctx || typeof Chart === 'undefined') return;
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{ data: data, backgroundColor: colors.slice(0, data.length), borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } }
      }
    }
  });
})();
</script>
<?php endif; ?>
