<div class="topbar">
  <h1>Envíos</h1>
</div>

<p class="form-hint" style="margin-top:0; margin-bottom:16px;">Tarifas por tramo de peso y zona geográfica (Blue Express). Configuradas en <code>app/models/Shipping.php</code>.</p>

<?php foreach ($modalities as $m): ?>
  <div class="card">
    <h2 style="margin-top:0;"><?= e(\App\Models\Shipping::modalityLabel($m)) ?></h2>
    <table class="data">
      <thead>
        <tr>
          <th>Talla</th>
          <?php foreach ($zones as $z): ?>
            <th><?= e(\App\Models\Shipping::zoneLabel($z)) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tiers as $t): ?>
          <tr>
            <td><?= e(\App\Models\Shipping::tierLabel($t)) ?></td>
            <?php foreach ($zones as $z): ?>
              <td><?= money($rates[$m][$z][$t]) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>

<div class="card">
  <h2 style="margin-top:0;">Regiones por zona</h2>
  <?php foreach ($zones as $z): ?>
    <p><strong><?= e(\App\Models\Shipping::zoneLabel($z)) ?>:</strong> <?= e(implode(', ', $zoneRegions[$z])) ?></p>
  <?php endforeach; ?>
</div>
