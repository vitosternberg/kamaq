<div class="topbar">
  <h1>Envíos</h1>
</div>

<div class="card">
  <h2 style="margin-top:0;">Política de envío</h2>
  <p class="form-hint" style="margin-top:0; margin-bottom:16px;">Estos montos determinan el costo de envío según el domicilio del cliente y el total de su compra.</p>
  <form method="post" action="<?= url('admin/envios/politica') ?>">
    <?= csrf_field() ?>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px;">
      <div class="form-group">
        <label>Envío estándar RM</label>
        <input type="number" step="0.01" min="0" name="rm_price" class="form-control" value="<?= (float) $shipping['rm_price'] ?>">
      </div>
      <div class="form-group">
        <label>Gratis desde (RM)</label>
        <input type="number" step="0.01" min="0" name="free_threshold" class="form-control" value="<?= (float) $shipping['free_threshold'] ?>">
      </div>
      <div class="form-group">
        <label>Express (RM)</label>
        <input type="number" step="0.01" min="0" name="express_price" class="form-control" value="<?= (float) $shipping['express_price'] ?>">
      </div>
      <div class="form-group">
        <label>Fuera de la RM</label>
        <input type="number" step="0.01" min="0" name="outside_price" class="form-control" value="<?= (float) $shipping['outside_price'] ?>">
      </div>
    </div>
    <button class="btn btn--primary" type="submit">Guardar política</button>
  </form>
</div>
