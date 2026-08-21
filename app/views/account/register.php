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
      <label>Ciudad</label>
      <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
    </div>
    <div class="form-group">
      <label>Dirección de envío</label>
      <input type="text" name="address" class="form-control" value="<?= e(old('address')) ?>">
    </div>
    <div class="form-check">
      <input type="checkbox" name="is_rm" id="is_rm" value="1" <?= old('is_rm') ? 'checked' : '' ?>>
      <label for="is_rm">Mi domicilio está en la Región Metropolitana (RM)</label>
    </div>
    <button class="btn btn--primary" type="submit">Crear cuenta</button>
  </form>
  <p style="margin-top:16px;">¿Ya tienes cuenta? <a href="<?= url('cuenta/ingresar') ?>">Inicia sesión</a></p>
</div>
