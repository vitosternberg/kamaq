<h1>Nueva contraseña</h1>

<div class="auth-box">
  <form method="post" action="<?= url('cuenta/recuperar/' . e($token)) ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Nueva contraseña</label>
      <input type="password" name="password" class="form-control" required>
      <div class="form-hint">Mínimo 6 caracteres.</div>
    </div>
    <button class="btn btn--primary" type="submit">Guardar contraseña</button>
  </form>
</div>
