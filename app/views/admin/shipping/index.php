<div class="topbar">
  <h1>Envíos</h1>
</div>

<div class="card">
  <h2 style="margin-top:0;">Agregar método</h2>
  <form method="post" action="<?= url('admin/envios/guardar') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
    <?= csrf_field() ?>
    <div class="form-group" style="margin:0;">
      <label>Nombre</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Precio</label>
      <input type="number" step="0.01" min="0" name="price" class="form-control" value="0">
    </div>
    <div class="form-group" style="margin:0;">
      <label>Orden</label>
      <input type="number" name="sort_order" class="form-control" value="0" style="max-width:80px;">
    </div>
    <button class="btn btn--primary" type="submit">Agregar</button>
  </form>
</div>

<div class="card">
  <h2 style="margin-top:0;">Métodos configurados</h2>
  <?php if (empty($methods)): ?>
    <p>No hay métodos de envío todavía. Agrega uno arriba.</p>
  <?php endif; ?>

  <?php foreach ($methods as $m): ?>
    <form method="post" action="<?= url('admin/envios/actualizar/' . (int) $m['id']) ?>" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:12px 0; border-bottom:1px solid var(--border);">
      <?= csrf_field() ?>
      <input type="text" name="name" class="form-control" value="<?= e($m['name']) ?>" style="max-width:220px;" required>
      <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= e($m['price']) ?>" style="max-width:120px;">
      <label style="display:flex; align-items:center; gap:6px; font-size:14px;">
        <input type="checkbox" name="is_active" <?= $m['is_active'] ? 'checked' : '' ?>> Activo
      </label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int) $m['sort_order'] ?>" style="max-width:80px;" title="Orden">
      <button class="btn btn--primary btn--sm" type="submit">Guardar</button>
      <button class="btn btn--danger btn--sm" type="submit" form="del<?= (int) $m['id'] ?>">Eliminar</button>
    </form>
    <form method="post" action="<?= url('admin/envios/eliminar/' . (int) $m['id']) ?>" id="del<?= (int) $m['id'] ?>" onsubmit="return confirm('¿Eliminar este método de envío?');" style="display:none;"><?= csrf_field() ?></form>
  <?php endforeach; ?>
</div>
