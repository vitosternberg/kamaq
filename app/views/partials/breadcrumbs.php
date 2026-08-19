<?php if (!empty($breadcrumbs)): ?>
<nav class="breadcrumbs" aria-label="Miga de pan">
  <ol>
    <?php $last = count($breadcrumbs) - 1; foreach ($breadcrumbs as $i => $crumb): ?>
      <li>
        <?php if ($i < $last && !empty($crumb['url'])): ?>
          <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
        <?php else: ?>
          <span aria-current="page"><?= e($crumb['label']) ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
<?php endif; ?>
