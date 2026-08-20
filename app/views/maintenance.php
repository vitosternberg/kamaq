<?php
date_default_timezone_set('America/Santiago');
$siteName = (string) config('app_name', 'KAMAQ');
$adsId = (string) config('ga_ads_id', '');
$launchAt = (string) config('launch_at', '');
$targetTs = $launchAt !== '' ? strtotime($launchAt) : time() + 86400;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($siteName) ?> — Próximamente</title>
<?php if ($adsId !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($adsId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($adsId) ?>');
</script>
<?php endif; ?>
<style>
  :root { --brand: #8a5a2b; --accent: #c9a15a; --text: #2b2b2b; --muted: #6b6b6b; }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    color: var(--text);
    background: linear-gradient(135deg, #fdf8ef, #f3e6d3);
    padding: 24px;
    text-align: center;
  }
  .wrap { max-width: 640px; width: 100%; }
  .brand {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: 6px;
    color: var(--brand);
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .rule { width: 56px; height: 3px; background: var(--accent); margin: 16px auto 24px; border-radius: 2px; }
  h1 { font-size: 34px; margin: 0 0 10px; }
  p.sub { color: var(--muted); font-size: 17px; margin: 0 0 36px; line-height: 1.5; }
  .countdown { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
  .unit {
    background: #fff;
    border: 1px solid #e8e0d4;
    border-radius: 12px;
    min-width: 92px;
    padding: 18px 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
  }
  .unit .num { font-size: 42px; font-weight: 800; color: var(--brand); line-height: 1; font-variant-numeric: tabular-nums; }
  .unit .label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-top: 8px; }
  .launch { margin-top: 32px; color: var(--muted); font-size: 14px; }
  @media (max-width: 480px) {
    h1 { font-size: 26px; }
    .unit { min-width: 72px; padding: 14px 8px; }
    .unit .num { font-size: 30px; }
  }
</style>
</head>
<body>
  <div class="wrap">
    <div class="brand"><?= e($siteName) ?></div>
    <div class="rule"></div>
    <h1>Estamos en mantenimiento</h1>
    <p class="sub">Estamos preparando algo especial.<br>Muy pronto abriremos nuestras puertas.</p>
    <div class="countdown" id="countdown">
      <div class="unit"><div class="num" id="cd-days">--</div><div class="label">Días</div></div>
      <div class="unit"><div class="num" id="cd-hours">--</div><div class="label">Horas</div></div>
      <div class="unit"><div class="num" id="cd-mins">--</div><div class="label">Min</div></div>
      <div class="unit"><div class="num" id="cd-secs">--</div><div class="label">Seg</div></div>
    </div>
    <p class="launch">Lanzamiento: <?= e(date('d/m/Y', $targetTs)) ?> a las <?= e(date('H:i', $targetTs)) ?> hrs</p>
  </div>

  <script>
  (function () {
    var target = <?= (int) $targetTs ?> * 1000;
    var d = document.getElementById('cd-days');
    var h = document.getElementById('cd-hours');
    var m = document.getElementById('cd-mins');
    var s = document.getElementById('cd-secs');
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
      var diff = target - Date.now();
      if (diff <= 0) {
        d.textContent = '00'; h.textContent = '00'; m.textContent = '00'; s.textContent = '00';
        return;
      }
      var sec = Math.floor(diff / 1000);
      d.textContent = pad(Math.floor(sec / 86400));
      h.textContent = pad(Math.floor((sec % 86400) / 3600));
      m.textContent = pad(Math.floor((sec % 3600) / 60));
      s.textContent = pad(sec % 60);
    }
    tick();
    setInterval(tick, 1000);
  })();
  </script>
</body>
</html>
