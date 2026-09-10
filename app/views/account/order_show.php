<div class="account-card__head">
  <h1>Pedido <?= e($order['order_number']) ?></h1>
  <a class="btn btn--outline btn--sm" href="<?= url('cuenta/pedidos') ?>">Volver</a>
</div>

<div class="account-layout">
  <?php include BASE_PATH . '/app/views/account/_nav.php'; ?>

  <div class="account-content">
    <div class="account-grid">
      <section class="account-card">
        <h2>Estado del pedido</h2>
        <p>Estado: <span class="account-badge account-badge--<?= e($order['status']) ?>"><?= e(order_status_label((string) $order['status'])) ?></span></p>
        <p>Pago: <strong><?= e(order_status_label((string) ($order['payment_status'] ?? 'pendiente'))) ?></strong></p>
        <p>Fecha: <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
        <p>Medio de pago: <?= e(payment_method_label((string) ($order['payment_method'] ?? ''))) ?></p>
        <?php if (!empty($order['shipping_method'])): ?>
          <p>Envío: <?= e($order['shipping_method']) ?></p>
        <?php endif; ?>
      </section>

      <section class="account-card">
        <h2>Entrega</h2>
        <p><strong><?= e($order['customer_name']) ?></strong></p>
        <p><?= e($order['customer_email']) ?></p>
        <?php if (!empty($order['customer_phone'])): ?><p><?= e($order['customer_phone']) ?></p><?php endif; ?>
        <?php if (!empty($order['address'])): ?>
          <p><?= e($order['address']) ?>, <?= e($order['city']) ?> <?= e($order['region']) ?></p>
        <?php endif; ?>
      </section>
    </div>

    <section class="account-card">
      <h2>Productos</h2>
      <table class="account-table">
        <thead>
          <tr><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($order['items'] as $item): ?>
            <tr>
              <td><?= e($item['product_name']) ?></td>
              <td><?= (int) $item['quantity'] ?></td>
              <td><?= money((float) $item['subtotal']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="account-totals">
        <p>Subtotal: <?= money((float) $order['subtotal']) ?></p>
        <?php if (!empty($order['tax'])): ?><p>Impuestos: <?= money((float) $order['tax']) ?></p><?php endif; ?>
        <p>Envío: <?= money((float) $order['shipping']) ?></p>
        <p class="account-totals__total"><strong>Total: <?= money((float) $order['total']) ?></strong></p>
      </div>
    </section>
  </div>
</div>
