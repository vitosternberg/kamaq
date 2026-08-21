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

  // Carrusel del hero (destacados)
  var hero = document.querySelector('[data-hero]');
  if (hero) {
    var slides = hero.querySelectorAll('[data-slide]');
    var dots = hero.querySelectorAll('[data-hero-dot]');
    var prev = hero.querySelector('[data-hero-prev]');
    var next = hero.querySelector('[data-hero-next]');
    var index = 0;
    var timer = null;

    function show(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-active', k === index); });
      dots.forEach(function (d, k) { d.classList.toggle('is-active', k === index); });
    }
    function restart() {
      window.clearInterval(timer);
      if (slides.length > 1) {
        timer = window.setInterval(function () { show(index + 1); }, 5000);
      }
    }
    if (prev) { prev.addEventListener('click', function () { show(index - 1); restart(); }); }
    if (next) { next.addEventListener('click', function () { show(index + 1); restart(); }); }
    dots.forEach(function (d, k) {
      d.addEventListener('click', function () { show(k); restart(); });
    });
    restart();
  }

  // Cierre automático de mensajes flash (opcional)
  window.setTimeout(function () {
    document.querySelectorAll('.flash').forEach(function (f) {
      f.style.transition = 'opacity .5s';
      f.style.opacity = '0';
      window.setTimeout(function () { f.remove(); }, 500);
    });
  }, 4000);
});
