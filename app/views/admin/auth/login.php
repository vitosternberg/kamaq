<div class="login-box card">
  <h1>Iniciar sesión</h1>
  <form method="post" action="<?= url('admin/login') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required autofocus>
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn--primary" style="width:100%;">Entrar</button>
  </form>
</div>
