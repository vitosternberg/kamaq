<div class="hero">
  <h1>¡Gracias por tu pedido!</h1>
  <?php if ($paymentMethod === 'transferencia'): ?>
    <p>Recibimos tu pedido. Realiza la transferencia indicada abajo para confirmar tu compra.</p>
  <?php else: ?>
    <p>Gracias por tu compra. Recibimos tu pago correctamente y te confirmaremos el despacho.</p>
  <?php endif; ?>
  <a class="btn btn--primary" href="<?= url('') ?>">Volver al inicio</a>
</div>

<?php if ($paymentMethod === 'transferencia'): ?>
  <div class="card" style="border:1px solid var(--border); border-radius:var(--radius); padding:20px; background:#fff; max-width:560px; margin:0 auto;">
    <h2 style="margin-top:0;">Paga por transferencia</h2>
    <p style="margin-top:0;">Reservamos tu pedido por 30 minutos mientras confirmamos tu transferencia.</p>
    <p>Titular: <strong><?= e($transfer['holder']) ?></strong></p>
    <p>RUT: <strong><?= e($transfer['rut']) ?></strong></p>
    <p>Banco: <strong><?= e($transfer['bank']) ?></strong></p>
    <p>Tipo de cuenta: <strong><?= e($transfer['account_type']) ?></strong></p>
    <p>Número de cuenta: <strong><?= e($transfer['account_number']) ?></strong></p>
    <p>Envía el comprobante a: <strong><?= e($transfer['email']) ?></strong></p>
    <p style="margin-bottom:0; font-size:14px; color:#666;">Tu pedido queda reservado con tu nombre y número de orden. Si no recibimos la transferencia en el plazo indicado, el pedido se cancela automáticamente y liberamos los productos.</p>
  </div>
<?php endif; ?>

<?php if (!empty($order) && $paymentMethod === 'webpay' && !empty($adsConversionId)): ?>
<script>
  gtag('event', 'conversion', {
      'send_to': '<?= e($adsConversionId) ?>',
      'value': <?= (float) $order['total'] ?>,
      'currency': 'CLP',
      'transaction_id': '<?= e($order['order_number']) ?>',
      'new_customer': <?= !empty($order['new_customer']) ? 'true' : 'false' ?>
  });
</script>
<?php endif; ?>
