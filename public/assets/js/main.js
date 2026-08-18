// KAMAQ — scripts del sitio público
document.addEventListener('DOMContentLoaded', function () {
  // Galería de producto: cambia la imagen principal al hacer clic en una miniatura
  document.querySelectorAll('.product-gallery__thumbs img').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      var main = document.querySelector('.product-gallery__main img');
      if (main) {
        main.src = this.getAttribute('data-full') || this.src;
        main.alt = this.alt;
      }
      document.querySelectorAll('.product-gallery__thumbs img').forEach(function (t) {
        t.classList.remove('active');
      });
      this.classList.add('active');
    });
  });

  // Cierre automático de mensajes flash (opcional)
  window.setTimeout(function () {
    document.querySelectorAll('.flash').forEach(function (f) {
      f.style.transition = 'opacity .5s';
      f.style.opacity = '0';
      window.setTimeout(function () { f.remove(); }, 500);
    });
  }, 4000);
});
