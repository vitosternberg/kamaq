<div class="topbar">
  <h1>Productos</h1>
  <a class="btn btn--primary" href="<?= url('admin/productos/crear') ?>">Nuevo producto</a>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th></th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Destacado</th><th>Super venta</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><img class="thumb" src="<?= e(!empty($p['cover']) ? upload('products/' . $p['cover']) : asset('img/placeholder.svg')) ?>" alt=""></td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['category_name'] ?? '—') ?></td>
        <td><?= money($p['price']) ?></td>
        <td><?= (int) $p['stock'] ?></td>
        <td><span class="badge badge--<?= $p['is_active'] ? 'ok' : 'off' ?>"><?= $p['is_active'] ? 'Activo' : 'Inactivo' ?></span></td>
        <td>
          <form method="post" action="<?= url('admin/productos/destacado/' . (int) $p['id']) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button class="btn btn--sm <?= $p['is_featured'] ? 'btn--primary' : 'btn--outline' ?>" type="submit"><?= $p['is_featured'] ? '★ Destacado' : '☆ Destacar' ?></button>
          </form>
        </td>
        <td>
          <form method="post" action="<?= url('admin/productos/superventa/' . (int) $p['id']) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button class="btn btn--sm <?= $p['is_bestseller'] ? 'btn--primary' : 'btn--outline' ?>" type="submit"><?= $p['is_bestseller'] ? '★ Super venta' : '☆ Super venta' ?></button>
          </form>
        </td>
        <td>
          <a class="btn btn--outline btn--sm" href="<?= url('admin/productos/editar/' . (int) $p['id']) ?>">Editar</a>
          <form method="post" action="<?= url('admin/productos/eliminar/' . (int) $p['id']) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar este producto?');">
            <?= csrf_field() ?>
            <button class="btn btn--danger btn--sm" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
