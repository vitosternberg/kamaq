<div class="topbar">
  <h1>Impuestos</h1>
</div>

<div class="card">
  <h2 style="margin-top:0;">Nuevo impuesto</h2>
  <form method="post" action="<?= url('admin/impuestos/guardar') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
    <?= csrf_field() ?>
    <div class="form-group" style="margin:0;">
      <label>Nombre</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Tasa (%)</label>
      <input type="number" step="0.01" name="rate" class="form-control" style="max-width:120px;" required>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Tipo</label>
      <input type="text" name="type" class="form-control" style="max-width:120px;" placeholder="IVA" value="IVA">
    </div>
    <div class="form-group" style="margin:0;">
      <label>Orden</label>
      <input type="number" name="sort_order" class="form-control" style="max-width:90px;" value="0">
    </div>
    <div class="form-check" style="margin:0;">
      <input type="checkbox" name="is_active" id="is_active" checked>
      <label for="is_active">Activo</label>
    </div>
    <button class="btn btn--primary" type="submit">Agregar</button>
  </form>
</div>

<div style="display:none;">
  <?php foreach ($taxes as $t): ?>
    <form method="post" action="<?= url('admin/impuestos/actualizar/' . (int) $t['id']) ?>" id="tf<?= (int) $t['id'] ?>">
      <?= csrf_field() ?>
    </form>
  <?php endforeach; ?>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th>Nombre</th><th>Tasa</th><th>Tipo</th><th>Orden</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($taxes as $t): ?>
      <tr>
        <td><input type="text" name="name" form="tf<?= (int) $t['id'] ?>" class="form-control" value="<?= e($t['name']) ?>" required></td>
        <td><input type="number" step="0.01" name="rate" form="tf<?= (int) $t['id'] ?>" class="form-control" value="<?= e($t['rate']) ?>" style="max-width:110px;" required></td>
        <td><input type="text" name="type" form="tf<?= (int) $t['id'] ?>" class="form-control" value="<?= e($t['type']) ?>" style="max-width:110px;"></td>
        <td><input type="number" name="sort_order" form="tf<?= (int) $t['id'] ?>" class="form-control" value="<?= (int) $t['sort_order'] ?>" style="max-width:80px;"></td>
        <td>
          <label style="display:flex; gap:6px; align-items:center; font-weight:400;">
            <input type="checkbox" name="is_active" form="tf<?= (int) $t['id'] ?>" <?= !empty($t['is_active']) ? 'checked' : '' ?>>
            Activo
          </label>
        </td>
        <td>
          <button class="btn btn--primary btn--sm" type="submit" form="tf<?= (int) $t['id'] ?>">Guardar</button>
          <form method="post" action="<?= url('admin/impuestos/eliminar/' . (int) $t['id']) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar este impuesto?');">
            <?= csrf_field() ?>
            <button class="btn btn--danger btn--sm" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($taxes)): ?><p>No hay impuestos definidos.</p><?php endif; ?>
</div>
