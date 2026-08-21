<?php
$baseUrl = url('admin/categorias');
$pageUrl = function (int $p) use ($baseUrl, $perPage): string {
    return $baseUrl . '?page=' . $p . '&per_page=' . (int) $perPage;
};
$windowStart = max(1, $page - 2);
$windowEnd = min($totalPages, $page + 2);
?>
<div class="topbar">
  <h1>Categorías</h1>
  <a class="btn btn--primary" href="<?= url('admin/categorias/crear') ?>">Nueva categoría</a>
</div>

<div class="card">
  <div class="table-toolbar">
    <form method="get" action="<?= $baseUrl ?>" class="perpage">
      <label for="per_page">Mostrar</label>
      <select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
        <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15</option>
        <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
        <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
      </select>
      <span>categorías por página</span>
    </form>

    <span class="table-meta">Página <?= $page ?> de <?= $totalPages ?></span>
  </div>

  <table class="data">
    <thead><tr><th>Nombre</th><th>Slug</th><th>Productos</th><th>Orden</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= str_repeat('— ', (int) ($c['depth'] ?? 0)) . e($c['name']) ?></td>
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

  <?php if ($totalPages > 1): ?>
    <nav class="pagination">
      <?php if ($page > 1): ?>
        <a class="btn btn--outline btn--sm" href="<?= $pageUrl($page - 1) ?>">Anterior</a>
      <?php endif; ?>

      <?php if ($windowStart > 1): ?>
        <a class="btn btn--outline btn--sm" href="<?= $pageUrl(1) ?>">1</a>
        <?php if ($windowStart > 2): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
        <a class="btn btn--sm <?= $i === $page ? 'btn--primary' : 'btn--outline' ?>" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($windowEnd < $totalPages): ?>
        <?php if ($windowEnd < $totalPages - 1): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
        <a class="btn btn--outline btn--sm" href="<?= $pageUrl($totalPages) ?>"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn--outline btn--sm" href="<?= $pageUrl($page + 1) ?>">Siguiente</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
