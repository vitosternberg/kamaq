<?php $isEdit = $category !== null; ?>
<div class="topbar">
  <h1><?= $isEdit ? 'Editar categoría' : 'Nueva categoría' ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/categorias') ?>">Volver</a>
</div>

<div class="card" style="max-width:640px;">
  <form method="post" action="<?= $isEdit ? url('admin/categorias/actualizar/' . (int) $category['id']) : url('admin/categorias/guardar') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nombre <?= help('Nombre visible de la categoría.') ?></label>
      <input type="text" name="name" class="form-control" value="<?= e($category['name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Slug (URL) <?= help('Fragmento de la URL; se genera si lo dejas vacío.') ?></label>
      <input type="text" name="slug" class="form-control" value="<?= e($category['slug'] ?? '') ?>">
      <div class="form-hint">Se genera automáticamente si lo dejas vacío.</div>
    </div>
    <div class="form-group">
      <label>Categoría padre <?= help('Categoría superior para armar la jerarquía.') ?></label>
      <select name="parent_id" class="form-control">
        <option value="">— Ninguna —</option>
        <?php foreach ($parents as $p): if (in_array((int) $p['id'], $excludeIds, true)) { continue; } ?>
          <option value="<?= (int) $p['id'] ?>" <?= ($category && (int) ($category['parent_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>><?= str_repeat('— ', (int) ($p['depth'] ?? 0)) . e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Descripción <?= help('Descripción opcional de la categoría.') ?></label>
      <textarea name="description" class="form-control"><?= e($category['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Meta título (SEO) <?= help('Título que muestra Google en los resultados.') ?></label>
      <input type="text" name="meta_title" class="form-control" value="<?= e($category['meta_title'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Meta descripción (SEO) <?= help('Descripción que muestra Google; ideal hasta ~155 caracteres.') ?></label>
      <textarea name="meta_description" class="form-control"><?= e($category['meta_description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Orden <?= help('Posición de orden en menús y listados.') ?></label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int) ($category['sort_order'] ?? 0) ?>">
    </div>
    <div class="form-check">
      <input type="checkbox" name="is_active" id="is_active" <?= (!$category || $category['is_active']) ? 'checked' : '' ?>>
      <label for="is_active">Categoría activa <?= help('Determina si la categoría es visible en la tienda.') ?></label>
    </div>
    <button class="btn btn--primary" type="submit">Guardar</button>
  </form>
</div>
