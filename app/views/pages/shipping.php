<h1>Política de envío</h1>

<div class="card" style="border:1px solid var(--border); border-radius:var(--radius); padding:24px; background:#fff;">
  <p>El costo de envío se calcula según el <strong>peso total</strong> de tu pedido y la <strong>zona</strong> de tu domicilio. Ofrecemos dos modalidades: <strong>despacho a domicilio</strong> y <strong>PUDO</strong> (retiro en puntos Blue Express o estaciones Copec).</p>

  <h2>Tramos de peso</h2>
  <ul>
    <?php foreach (\App\Models\Shipping::TIERS as $t): ?>
      <li><?= e(\App\Models\Shipping::tierLabel($t)) ?></li>
    <?php endforeach; ?>
  </ul>

  <h2>Zonas</h2>
  <ul>
    <?php foreach (\App\Models\Shipping::ZONES as $z): ?>
      <li><strong><?= e(\App\Models\Shipping::zoneLabel($z)) ?>:</strong> <?= e(implode(', ', \App\Models\Shipping::ZONE_REGIONS[$z])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php foreach (\App\Models\Shipping::MODALITIES as $m): ?>
    <h2><?= e(\App\Models\Shipping::modalityLabel($m)) ?></h2>
    <table class="cart-table" style="margin-bottom:20px;">
      <thead>
        <tr>
          <th>Talla</th>
          <?php foreach (\App\Models\Shipping::ZONES as $z): ?>
            <th><?= e(\App\Models\Shipping::zoneLabel($z)) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach (\App\Models\Shipping::TIERS as $t): ?>
          <tr>
            <td><?= e($t) ?></td>
            <?php foreach (\App\Models\Shipping::ZONES as $z): ?>
              <td><?= money(\App\Models\Shipping::RATES[$m][$z][$t]) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>

  <p style="color:var(--muted);">Los tiempos de despacho y el seguimiento se coordinan por correo una vez confirmado el pago.</p>
</div>
