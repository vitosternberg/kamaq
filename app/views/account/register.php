<h1>Crear cuenta</h1>

<div class="auth-box">
  <form method="post" action="<?= url('cuenta/registro') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
      <label>Tipo de documento</label>
      <select name="customer_type" id="customer-type" class="form-control">
        <option value="persona_natural" <?= old('customer_type', 'persona_natural') === 'persona_natural' ? 'selected' : '' ?>>Boleta (persona natural)</option>
        <option value="empresa" <?= old('customer_type', 'persona_natural') === 'empresa' ? 'selected' : '' ?>>Factura (empresa)</option>
      </select>
    </div>

    <div id="company-fields" style="display:none;">
      <div class="form-group">
        <label>RUT empresa *</label>
        <input type="text" name="company_rut" class="form-control" value="<?= e(old('company_rut')) ?>" placeholder="76.123.456-K">
      </div>
      <div class="form-group">
        <label>Razón social *</label>
        <input type="text" name="company_name" class="form-control" value="<?= e(old('company_name')) ?>">
      </div>
      <div class="form-group">
        <label>Giro *</label>
        <input type="text" name="giro" class="form-control" value="<?= e(old('giro')) ?>" placeholder="Ej.: Fabricación de muebles">
      </div>
      <div class="form-group">
        <label>Dirección de la empresa</label>
        <input type="text" name="company_address" class="form-control" value="<?= e(old('company_address')) ?>">
      </div>
      <div class="form-group">
        <label>Correo de la empresa</label>
        <input type="email" name="company_email" class="form-control" value="<?= e(old('company_email')) ?>">
      </div>
      <div class="form-group">
        <label>Teléfono de la empresa</label>
        <input type="text" name="company_phone" class="form-control" value="<?= e(old('company_phone')) ?>">
      </div>
      <hr>
      <p style="margin:0 0 12px;"><strong>Datos del representante</strong> (quien recibe las cotizaciones)</p>
    </div>

    <div class="form-group">
      <label>Nombre *</label>
      <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
    </div>
    <div class="form-group">
      <label>Apellido *</label>
      <input type="text" name="lastname" class="form-control" value="<?= e(old('lastname')) ?>" required>
    </div>
    <div class="form-group">
      <label>RUT personal</label>
      <input type="text" name="rut" class="form-control" value="<?= e(old('rut')) ?>" placeholder="12.345.678-9">
    </div>
    <div class="form-group">
      <label>Email *</label>
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

    <div id="person-fields">
      <div class="form-group">
        <label>Región</label>
        <select name="region" id="region" class="form-control">
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
    </div>

    <button class="btn btn--primary" type="submit">Crear cuenta</button>
  </form>
  <p style="margin-top:16px;">¿Ya tienes cuenta? <a href="<?= url('cuenta/ingresar') ?>">Inicia sesión</a></p>
</div>

<script>
(function () {
  var typeSel = document.getElementById('customer-type');
  var companyFields = document.getElementById('company-fields');
  var personFields = document.getElementById('person-fields');
  var regionSel = document.getElementById('region');

  function toggle() {
    var isCompany = typeSel.value === 'empresa';
    companyFields.style.display = isCompany ? '' : 'none';
    personFields.style.display = isCompany ? 'none' : '';
    regionSel.required = !isCompany;
  }
  if (typeSel) {
    typeSel.addEventListener('change', toggle);
    toggle();
  }

  var COMMUNES = <?= json_encode(chile_communes(), JSON_UNESCAPED_UNICODE) ?>;
  var oldRegion = <?= json_encode((string) old('region')) ?>;
  var oldCity = <?= json_encode((string) old('city')) ?>;
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
