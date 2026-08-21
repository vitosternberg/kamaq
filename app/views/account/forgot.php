<h1>Recuperar contraseña</h1>

<div class="auth-box">
  <p style="margin-top:0;">Ingresa tu correo y te enviaremos un enlace para cambiar tu contraseña.</p>
  <form method="post" action="<?= url('cuenta/olvide') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <button class="btn btn--primary" type="submit">Enviar enlace</button>
  </form>
  <p style="margin-top:16px;"><a href="<?= url('cuenta/ingresar') ?>">Volver a iniciar sesión</a></p>
</div>
