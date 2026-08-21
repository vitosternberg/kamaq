<h1>Política de envío</h1>

<div class="card" style="border:1px solid var(--border); border-radius:var(--radius); padding:24px; background:#fff;">
  <p>El costo de envío se calcula automáticamente según la región de tu domicilio registrado y el total de tu compra.</p>

  <h2>Dentro de la Región Metropolitana (RM)</h2>
  <ul>
    <li><strong>Envío estándar:</strong> <?= money($shipping['rm_price']) ?>.</li>
    <li><strong>Envío gratis</strong> en compras desde <?= money($shipping['free_threshold']) ?>.</li>
    <li><strong>Envío Express:</strong> <?= money($shipping['express_price']) ?>.</li>
  </ul>

  <h2>Fuera de la Región Metropolitana</h2>
  <ul>
    <li><strong>Envío estándar:</strong> <?= money($shipping['outside_price']) ?>.</li>
  </ul>

  <p style="color:var(--muted);">Los tiempos de despacho y el seguimiento se coordinan por correo una vez confirmado el pago.</p>
</div>
