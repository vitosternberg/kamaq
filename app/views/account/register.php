<h1>Crear cuenta</h1>

<div class="auth-box">
  <form method="post" action="<?= url('cuenta/registro') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nombre completo</label>
      <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
      <div class="form-hint">Mínimo 6 caracteres.</div>
    </div>
    <div class="form-group">
      <label>Teléfono</label>
      <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
    </div>
    <div class="form-group">
      <label>Región</label>
      <select name="region" id="region" class="form-control" required>
        <option value="">— Selecciona tu región —</option>
        <?php foreach (chile_regions() as $r): ?>
          <option value="<?= e($r) ?>" <?= old('region') === $r ? 'selected' : '' ?>><?= e($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Comuna</label>
      <select name="city" id="city" class="form-control">
        <option value="">— Selecciona tu comuna —</option>
      </select>
    </div>
    <div class="form-group">
      <label>Dirección de envío</label>
      <input type="text" name="address" class="form-control" value="<?= e(old('address')) ?>" placeholder="Calle y número">
    </div>
    <button class="btn btn--primary" type="submit">Crear cuenta</button>
  </form>
  <p style="margin-top:16px;">¿Ya tienes cuenta? <a href="<?= url('cuenta/ingresar') ?>">Inicia sesión</a></p>
</div>

<script>
(function () {
  var COMMUNES = <?= json_encode(chile_communes(), JSON_UNESCAPED_UNICODE) ?>;
  var oldRegion = <?= json_encode((string) old('region')) ?>;
  var oldCity = <?= json_encode((string) old('city')) ?>;
  var regionSel = document.getElementById('region');
  var citySel = document.getElementById('city');
  if (!regionSel || !citySel) { return; }

  function fill() {
    var region = regionSel.value;
    var communes = COMMUNES[region] || [];
    citySel.innerHTML = '<option value="">— Selecciona tu comuna —</option>';
    communes.forEach(function (c) {
      var o = document.createElement('option');
      o.value = c;
      o.textContent = c;
      if (c === oldCity) { o.selected = true; }
      citySel.appendChild(o);
    });
  }
  regionSel.addEventListener('change', fill);
  if (oldRegion && regionSel.value === oldRegion) { fill(); }
})();
</script>
