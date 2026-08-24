<div class="topbar">
  <h1>Cotizaciones</h1>
  <a class="btn btn--primary" href="<?= url('admin/cotizaciones/crear') ?>">Nueva cotización</a>
</div>

<div class="card">
  <table class="data">
    <thead><tr><th>Nº</th><th>Empresa</th><th>Correo</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($quotes as $q): ?>
      <tr>
        <td><?= e($q['quote_number']) ?></td>
        <td><?= e($q['customer_company']) ?></td>
        <td><?= e($q['customer_email'] ?? '—') ?></td>
        <td><?= money($q['total']) ?></td>
        <td><?= quote_status_badge($q['status']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($q['created_at']))) ?></td>
        <td><a class="btn btn--outline btn--sm" href="<?= url('admin/cotizaciones/' . (int) $q['id']) ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($quotes)): ?><p>No hay cotizaciones todavía.</p><?php endif; ?>
</div>
