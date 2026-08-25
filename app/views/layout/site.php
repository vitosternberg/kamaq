<?php
$siteName = (string) config('app_name', 'KAMAQ');
$pageTitle = $pageTitle ?? $siteName;
$metaDescription = $metaDescription ?? 'Regalos personalizados y mementos para toda ocasión.';
$adsId = (string) config('ga_ads_id', '');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<?php if (!empty($canonical)): ?><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<?php if (!empty($breadcrumbs)): ?>
<script type="application/ld+json">
<?php
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
    $items = [];
    $pos = 1;
    foreach ($breadcrumbs as $crumb) {
        $item = ['@type' => 'ListItem', 'position' => $pos, 'name' => $crumb['label']];
        if (!empty($crumb['url'])) {
            $u = $crumb['url'];
            if (str_starts_with($u, '/')) {
                $u = $origin . $u;
            }
            $item['item'] = $u;
        }
        $items[] = $item;
        $pos++;
    }
    echo json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
</script>
<?php endif; ?>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php if ($adsId !== ''): ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($adsId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($adsId) ?>');
</script>
<?php endif; ?>
</head>
<body>
<?php include BASE_PATH . '/app/views/layout/header.php'; ?>
<main class="container">
  <?php include BASE_PATH . '/app/views/partials/breadcrumbs.php'; ?>
  <?php if ($msg = flash('success')): ?>
    <div class="flash flash--success"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="flash flash--error"><?= e($msg) ?></div>
  <?php endif; ?>
  <?= $content ?>
</main>
<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
<?php include BASE_PATH . '/app/views/partials/whatsapp_floating.php'; ?>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
