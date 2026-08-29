<div class="hero">
  <h1>¡Gracias por tu pedido!</h1>
  <p>Gracias por tu compra. Recibimos tu pago correctamente y te confirmaremos el despacho.</p>
  <a class="btn btn--primary" href="<?= url('') ?>">Volver al inicio</a>
</div>

<?php if (!empty($order) && !empty($adsConversionId)): ?>
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
