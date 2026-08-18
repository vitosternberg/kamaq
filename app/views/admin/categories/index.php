<div class="topbar">
  <h1>Categorías</h1>
  <a class="btn btn--primary" href="<?= url('admin/categorias/crear') ?>">Nueva categoría</a>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th>Nombre</th><th>Slug</th><th>Productos</th><th>Orden</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['slug']) ?></td>
        <td><?= (int) $c['product_count'] ?></td>
        <td><?= (int) $c['sort_order'] ?></td>
        <td><span class="badge badge--<?= $c['is_active'] ? 'ok' : 'off' ?>"><?= $c['is_active'] ? 'Activa' : 'Inactiva' ?></span></td>
        <td>
          <a class="btn btn--outline btn--sm" href="<?= url('admin/categorias/editar/' . (int) $c['id']) ?>">Editar</a>
          <form method="post" action="<?= url('admin/categorias/eliminar/' . (int) $c['id']) ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría?');">
            <?= csrf_field() ?>
            <button class="btn btn--danger btn--sm" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
