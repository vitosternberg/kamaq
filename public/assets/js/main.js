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

  // Autocompletado del buscador (sugerencias en vivo)
  var searchInput = document.getElementById('search-input');
  if (searchInput) {
    var searchBox = searchInput.closest('.header-search');
    var suggestUrl = searchInput.getAttribute('data-suggest');
    var productBase = searchInput.getAttribute('data-product-base');

    var dropdown = document.createElement('div');
    dropdown.className = 'search-suggestions';
    searchBox.appendChild(dropdown);

    function esc(s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    var debounce = null;
    searchInput.addEventListener('input', function () {
      window.clearTimeout(debounce);
      var q = searchInput.value.trim();
      if (q.length < 2) {
        dropdown.innerHTML = '';
        dropdown.classList.remove('is-open');
        return;
      }
      debounce = window.setTimeout(function () {
        fetch(suggestUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (r) { return r.json(); })
          .then(function (items) {
            if (!Array.isArray(items) || !items.length) {
              dropdown.innerHTML = '';
              dropdown.classList.remove('is-open');
              return;
            }
            dropdown.innerHTML = items.map(function (it) {
              return '<a href="' + productBase + '/' + esc(it.slug) + '">'
                + '<span class="ss-name">' + esc(it.name) + '</span>'
                + '<span class="ss-price">' + esc(it.price) + '</span>'
                + '</a>';
            }).join('');
            dropdown.classList.add('is-open');
          })
          .catch(function () {
            dropdown.classList.remove('is-open');
          });
      }, 300);
    });

    document.addEventListener('click', function (e) {
      if (!searchBox.contains(e.target)) {
        dropdown.classList.remove('is-open');
      }
    });
  }
});
