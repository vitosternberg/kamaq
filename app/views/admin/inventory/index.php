<?php
$statusMeta = [
    'ok' => ['Disponible', 'ok'],
    'bajo' => ['Bajo stock', 'warn'],
    'agotado' => ['Agotado', 'off'],
];
?>
<div class="topbar">
  <h1>Inventario</h1>
</div>

<div class="stats">
  <div class="stat"><div class="num"><?= (int) $counts['total'] ?></div><div class="label">Productos</div></div>
  <div class="stat"><div class="num"><?= (int) $counts['bajo'] ?></div><div class="label">Bajo stock</div></div>
  <div class="stat"><div class="num"><?= (int) $counts['agotado'] ?></div><div class="label">Agotados</div></div>
</div>

<div class="card">
  <form method="post" action="<?= url('admin/inventario/umbral') ?>" style="display:flex; gap:10px; align-items:flex-end;">
    <?= csrf_field() ?>
    <div class="form-group" style="margin:0;">
      <label>Umbral de bajo stock (alerta si stock ≤ este valor)</label>
      <input type="number" name="threshold" min="0" class="form-control" value="<?= (int) $threshold ?>" style="max-width:140px;">
    </div>
    <button class="btn btn--primary" type="submit">Guardar umbral</button>
  </form>
</div>

<div class="card">
  <h2 style="margin-top:0;">Ajustes en masa</h2>
  <form method="post" action="<?= url('admin/inventario/precios') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-bottom:14px;">
    <?= csrf_field() ?>
    <div class="form-group" style="margin:0;">
      <label>% precios (positivo sube, negativo baja)</label>
      <input type="number" step="0.1" name="percent" class="form-control" style="max-width:140px;" required>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Categoría (opcional)</label>
      <select name="category_id" class="form-control">
        <option value="">Todas</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn--primary" type="submit">Aplicar a precios</button>
  </form>

  <form method="post" action="<?= url('admin/inventario/stock') ?>" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
    <?= csrf_field() ?>
    <div class="form-group" style="margin:0;">
      <label>Stock (+ suma, − resta)</label>
      <input type="number" name="delta" class="form-control" style="max-width:140px;" required>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Categoría (opcional)</label>
      <select name="category_id" class="form-control">
        <option value="">Todas</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn--primary" type="submit">Aplicar a stock</button>
  </form>
</div>

<div class="topbar" style="margin-top:20px;">
  <div style="display:flex; gap:8px;">
    <a class="btn <?= $filter === '' ? 'btn--primary' : 'btn--outline' ?>" href="<?= url('admin/inventario') ?>">Todos</a>
    <a class="btn <?= $filter === 'bajo' ? 'btn--primary' : 'btn--outline' ?>" href="<?= url('admin/inventario') ?>?status=bajo">Bajo stock</a>
    <a class="btn <?= $filter === 'agotado' ? 'btn--primary' : 'btn--outline' ?>" href="<?= url('admin/inventario') ?>?status=agotado">Agotados</a>
  </div>
</div>

<div style="display:none;">
  <?php foreach ($products as $p): ?>
    <form method="post" action="<?= url('admin/inventario/actualizar') ?>" id="f<?= (int) $p['id'] ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
    </form>
  <?php endforeach; ?>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th>Producto</th><th>SKU</th><th>Stock</th><th>Precio</th><th>Oferta</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): $meta = $statusMeta[$p['stock_status']] ?? $statusMeta['ok']; ?>
      <tr>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['sku'] ?? '') ?></td>
        <td><input type="number" name="stock" form="f<?= (int) $p['id'] ?>" class="form-control" value="<?= (int) $p['stock'] ?>" style="max-width:80px;"></td>
        <td><input type="number" step="0.01" name="price" form="f<?= (int) $p['id'] ?>" class="form-control" value="<?= e($p['price']) ?>" style="max-width:110px;"></td>
        <td><input type="number" step="0.01" name="sale_price" form="f<?= (int) $p['id'] ?>" class="form-control" value="<?= e($p['sale_price'] ?? '') ?>" style="max-width:110px;"></td>
        <td><span class="badge badge--<?= e($meta[1]) ?>"><?= e($meta[0]) ?></span></td>
        <td><button class="btn btn--primary btn--sm" type="submit" form="f<?= (int) $p['id'] ?>">Guardar</button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($products)): ?><p>No hay productos.</p><?php endif; ?>
</div>
