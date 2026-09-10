<h1>Mis pedidos</h1>

<div class="account-layout">
  <?php include BASE_PATH . '/app/views/account/_nav.php'; ?>

  <div class="account-content">
    <section class="account-card">
      <?php if (!empty($orders)): ?>
        <table class="account-table">
          <thead>
            <tr><th>Nº pedido</th><th>Fecha</th><th>Total</th><th>Pago</th><th>Estado</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                <td><?= money((float) $o['total']) ?></td>
                <td><?= e(order_status_label((string) ($o['payment_status'] ?? 'pendiente'))) ?></td>
                <td><span class="account-badge account-badge--<?= e($o['status']) ?>"><?= e(order_status_label((string) $o['status'])) ?></span></td>
                <td><a class="btn btn--outline btn--sm" href="<?= url('cuenta/pedidos/' . (int) $o['id']) ?>">Ver detalle</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="account-empty">No tienes pedidos todavía. <a href="<?= url('catalogo') ?>">Ir al catálogo</a></p>
      <?php endif; ?>
    </section>
  </div>
</div>
