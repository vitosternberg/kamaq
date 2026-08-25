<?php
$whatsapp = (string) config('whatsapp', '56932080779');
?>
<!-- Botón flotante de WhatsApp -->
<button type="button" id="wsp-fab" class="wsp-fab" aria-label="Contactar por WhatsApp">
  <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm0 18.15a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.23 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.29z"/></svg>
</button>

<!-- Modal de captura -->
<div id="wsp-modal" class="wsp-modal" hidden>
  <div class="wsp-modal__box" role="dialog" aria-modal="true" aria-labelledby="wsp-modal-title">
    <div class="wsp-modal__head">
      <h3 id="wsp-modal-title">Contacto por WhatsApp</h3>
      <button type="button" id="wsp-modal-close" class="wsp-modal__close" aria-label="Cerrar">&times;</button>
    </div>
    <form id="wsp-form" class="wsp-modal__form">
      <div class="form-group">
        <label for="wsp-name">Tu nombre *</label>
        <input type="text" id="wsp-name" name="name" class="form-control" required placeholder="Ej: Juan Pérez">
      </div>
      <div class="form-group">
        <label for="wsp-phone">Tu teléfono *</label>
        <input type="tel" id="wsp-phone" name="phone" class="form-control" required placeholder="Ej: 912345678" inputmode="numeric">
        <div class="form-hint">9 dígitos, sin el +56.</div>
      </div>
      <button type="submit" class="btn btn--primary" style="width:100%;">Enviar mensaje</button>
    </form>
  </div>
</div>

<script>
(function () {
  var fab = document.getElementById('wsp-fab');
  var modal = document.getElementById('wsp-modal');
  var closeBtn = document.getElementById('wsp-modal-close');
  var form = document.getElementById('wsp-form');
  var phoneEl = document.getElementById('wsp-phone');
  var nameEl = document.getElementById('wsp-name');

  function openModal() { if (modal) modal.hidden = false; }
  function closeModal() { if (modal) modal.hidden = true; }

  if (fab) fab.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

  if (phoneEl) {
    phoneEl.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 9);
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var phone = phoneEl ? phoneEl.value.replace(/\D/g, '') : '';
      var name = nameEl ? nameEl.value.trim() : '';

      if (phone.length !== 9) { alert('Ingresa un teléfono válido de 9 dígitos.'); return; }
      if (!name) { alert('Ingresa tu nombre.'); return; }

      fetch('<?= url('whatsapp/guardar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, phone: phone })
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data && data.success) {
            var message = encodeURIComponent('Hola, soy ' + name + '. Quiero información sobre sus productos y cotizaciones.');
            window.open('https://wa.me/<?= e($whatsapp) ?>?text=' + message, '_blank');
            closeModal();
            form.reset();
          } else {
            alert('No se pudo guardar tu contacto. Intenta nuevamente.');
          }
        })
        .catch(function () {
          alert('Error de conexión. Intenta nuevamente.');
        });
    });
  }
})();
</script>
