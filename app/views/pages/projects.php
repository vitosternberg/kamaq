<section class="page-hero" style="background-image: url('<?= asset('img/proyecto1.png') ?>');">
  <div class="page-hero__overlay"></div>
  <div class="page-hero__content">
    <h1>Tráenos tu diseño y nosotros nos encargamos</h1>
    <h2>Creamos junto contigo el proyecto para tu ocasión especial</h2>
  </div>
</section>

<div class="page-content">
  <p>Si tienes alguna imagen o idea de Pinterest o Instagram, podemos trabajar en el desarrollo del concepto para que tu ocasión especial sea aún más personalizada y que tus invitados tengan un recuerdo inolvidable. Nosotros evaluamos el costo y el tiempo y te entregamos una cotización para tu evaluación. Vamos a crear juntos.</p>

  <form method="post" action="<?= url('proyectos') ?>" enctype="multipart/form-data" style="margin-top:24px;">
    <?= csrf_field() ?>

    <h3>Cuéntanos tu proyecto</h3>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
      </div>
    </div>
    <div class="form-group">
      <label>Teléfono (opcional)</label>
      <input type="tel" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
    </div>

    <div class="form-group">
      <label>Título del proyecto</label>
      <input type="text" name="project_title" class="form-control" value="<?= e(old('project_title')) ?>" required>
    </div>

    <div class="form-group">
      <label>Categoría</label>
      <select name="category_id" class="form-control" required>
        <option value="">— Elige una categoría —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= ((string) old('category_id') === (string) $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Detalles del proyecto</label>
      <textarea name="details" class="form-control" required><?= e(old('details')) ?></textarea>
      <div class="form-hint">Cuéntanos qué imaginas: materiales, colores, cantidades, fecha del evento, etc.</div>
    </div>

    <div class="form-group">
      <label>Archivos adjuntos (imágenes o referencias)</label>
      <input type="file" name="attachments[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf">
      <div class="form-hint">Puedes subir una o más imágenes o un PDF (máx. 10 MB por archivo).</div>
    </div>

    <button class="btn btn--primary" type="submit">Enviar proyecto</button>
  </form>
</div>
