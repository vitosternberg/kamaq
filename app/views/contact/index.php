<h1>Contacto</h1>
<p>¿Necesitas un regalo personalizado o una cotización corporativa? Escríbenos.</p>

<form method="post" action="<?= url('contacto') ?>" style="max-width:560px;">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>Nombre</label>
    <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
  </div>
  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
  </div>
  <div class="form-group">
    <label>Mensaje</label>
    <textarea name="message" class="form-control" required><?= e(old('message')) ?></textarea>
  </div>
  <button class="btn btn--primary" type="submit">Enviar</button>
</form>
