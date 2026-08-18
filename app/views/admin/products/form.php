<?php $isEdit = $product !== null; ?>
<div class="topbar">
  <h1><?= $isEdit ? 'Editar producto' : 'Nuevo producto' ?></h1>
  <a class="btn btn--outline" href="<?= url('admin/productos') ?>">Volver</a>
</div>

<div class="card">
  <form method="post" action="<?= $isEdit ? url('admin/productos/actualizar/' . (int) $product['id']) : url('admin/productos/guardar') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group">
      <label>Nombre</label>
      <input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Slug (URL)</label>
      <input type="text" name="slug" class="form-control" value="<?= e($product['slug'] ?? '') ?>">
      <div class="form-hint">Se genera automáticamente si lo dejas vacío.</div>
    </div>
    <div class="form-group">
      <label>SKU</label>
      <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Categoría</label>
      <select name="category_id" class="form-control">
        <option value="">— Sin categoría —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= ($product && (int) ($product['category_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Descripción corta</label>
      <input type="text" name="short_description" class="form-control" value="<?= e($product['short_description'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Descripción</label>
      <textarea name="description" class="form-control"><?= e($product['description'] ?? '') ?></textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
      <div class="form-group">
        <label>Precio</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= e($product['price'] ?? '0') ?>" required>
      </div>
      <div class="form-group">
        <label>Precio oferta (opcional)</label>
        <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= e($product['sale_price'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Stock</label>
        <input type="number" name="stock" class="form-control" value="<?= (int) ($product['stock'] ?? 0) ?>">
      </div>
    </div>

    <div class="form-check">
      <input type="checkbox" name="is_featured" id="is_featured" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>>
      <label for="is_featured">Destacado</label>
    </div>
    <div class="form-check">
      <input type="checkbox" name="is_active" id="is_active" <?= (!$product || $product['is_active']) ? 'checked' : '' ?>>
      <label for="is_active">Producto activo</label>
    </div>

    <div class="form-group">
      <label>Meta título (SEO)</label>
      <input type="text" name="meta_title" class="form-control" value="<?= e($product['meta_title'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Meta descripción (SEO)</label>
      <textarea name="meta_description" class="form-control"><?= e($product['meta_description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Imágenes</label>
      <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
      <div class="form-hint">JPG, PNG o WebP. La primera imagen se usa como principal.</div>
    </div>

    <?php if (!empty($images)): ?>
      <div class="image-list">
        <?php foreach ($images as $img): ?>
          <div class="image-item">
            <img src="<?= e(upload('products/' . $img['filename'])) ?>" alt="" class="<?= $img['is_primary'] ? 'primary' : '' ?>">
            <div class="label"><?= $img['is_primary'] ? 'Principal' : '' ?></div>
            <form method="post" action="<?= url('admin/productos/imagen/eliminar/' . (int) $img['id']) ?>" onsubmit="return confirm('¿Eliminar esta imagen?');">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
              <button class="btn btn--danger btn--sm" type="submit" style="margin-top:4px;">Quitar</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <button class="btn btn--primary" type="submit" style="margin-top:16px;">Guardar</button>
  </form>
</div>
