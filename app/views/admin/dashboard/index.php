<h1>Panel</h1>

<div class="stats">
  <div class="stat"><div class="num"><?= (int) $stats['products'] ?></div><div class="label">Productos</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['categories'] ?></div><div class="label">Categorías</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['orders'] ?></div><div class="label">Pedidos</div></div>
  <div class="stat"><div class="num"><?= (int) $stats['customers'] ?></div><div class="label">Clientes</div></div>
</div>

<h2>Hero de inicio (Destacados)</h2>
<div class="card">
  <?php if (!empty($heroProducts)): ?>
    <div class="hero-manage">
      <?php foreach ($heroProducts as $hp): ?>
        <div class="hero-manage__item">
          <img src="<?= e(!empty($hp['cover']) ? upload('products/' . $hp['cover']) : asset('img/placeholder.svg')) ?>" alt="">
          <div class="hero-manage__info">
            <span class="hero-manage__name"><?= e($hp['name']) ?></span>
            <a href="<?= url('admin/productos/editar/' . (int) $hp['id']) ?>">Editar</a>
          </div>
          <form method="post" action="<?= url('admin/productos/destacado/' . (int) $hp['id']) ?>">
            <?= csrf_field() ?>
            <button class="btn btn--outline btn--sm" type="submit">Quitar</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="form-hint">Estos productos se muestran en el carrusel del inicio. Agrega o quita destacados desde <a href="<?= url('admin/productos') ?>">Productos</a>.</p>
  <?php else: ?>
    <p>No hay productos en el hero. Activa "Destacado" en <a href="<?= url('admin/productos') ?>">Productos</a> para que aparezcan aquí.</p>
  <?php endif; ?>
</div>

<h2>Últimos pedidos</h2>
<div class="card">
  <?php if (!empty($recentOrders)): ?>
    <table class="data">
      <thead><tr><th>Nº</th><th>Cliente</th><th>Total</th><th>Estado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td><?= e($o['order_number']) ?></td>
          <td><?= e($o['customer_name']) ?></td>
          <td><?= money($o['total']) ?></td>
          <td><span class="badge badge--<?= $o['status'] === 'pendiente' ? 'off' : 'ok' ?>"><?= e($o['status']) ?></span></td>
          <td><a class="btn btn--outline btn--sm" href="<?= url('admin/pedidos/' . (int) $o['id']) ?>">Ver</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay pedidos todavía.</p>
  <?php endif; ?>
</div>
