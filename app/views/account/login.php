<h1>Iniciar sesión</h1>

<div class="auth-box">
  <form method="post" action="<?= url('cuenta/ingresar') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn--primary" type="submit">Ingresar</button>
  </form>
  <p style="margin-top:16px;"><a href="<?= url('cuenta/olvide') ?>">¿Problemas con la contraseña?</a></p>
  <p>¿No tienes cuenta? <a href="<?= url('cuenta/registro') ?>">Créala aquí</a></p>
</div>
