<?php $isEdit = $category !== null; ?>
<div class="topbar">
  <h1><?= $isEdit ? 'Editar categoría' : 'Nueva categoría' ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/categorias') ?>">Volver</a>
</div>

<div class="card" style="max-width:640px;">
  <form method="post" action="<?= $isEdit ? url('admin/categorias/actualizar/' . (int) $category['id']) : url('admin/categorias/guardar') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nombre</label>
      <input type="text" name="name" class="form-control" value="<?= e($category['name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Slug (URL)</label>
      <input type="text" name="slug" class="form-control" value="<?= e($category['slug'] ?? '') ?>">
      <div class="form-hint">Se genera automáticamente si lo dejas vacío.</div>
    </div>
    <div class="form-group">
      <label>Categoría padre</label>
      <select name="parent_id" class="form-control">
        <option value="">— Ninguna —</option>
        <?php foreach ($parents as $p): if ($category && (int) $p['id'] === (int) $category['id']) { continue; } ?>
          <option value="<?= (int) $p['id'] ?>" <?= ($category && (int) ($category['parent_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Descripción</label>
      <textarea name="description" class="form-control"><?= e($category['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Meta título (SEO)</label>
      <input type="text" name="meta_title" class="form-control" value="<?= e($category['meta_title'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Meta descripción (SEO)</label>
      <textarea name="meta_description" class="form-control"><?= e($category['meta_description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Orden</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int) ($category['sort_order'] ?? 0) ?>">
    </div>
    <div class="form-check">
      <input type="checkbox" name="is_active" id="is_active" <?= (!$category || $category['is_active']) ? 'checked' : '' ?>>
      <label for="is_active">Categoría activa</label>
    </div>
    <button class="btn btn--primary" type="submit">Guardar</button>
  </form>
</div>
