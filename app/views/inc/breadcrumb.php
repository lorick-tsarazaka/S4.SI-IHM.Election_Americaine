<?php
    $base = defined('BASE_URL') ? BASE_URL : '';

    $crumbs = $breadcrumbs ?? [];

    if (empty($crumbs)) {
        $label = isset($pageTitle) ? $pageTitle : (isset($page_title) ? $page_title : 'Accueil');
        $crumbs = [
            ['label' => 'Accueil', 'url' => '/'],
            ['label' => $label]
        ];
    }
?>

<nav aria-label="breadcrumb" class="hero-breadcrumb">
    <ol class="breadcrumb breadcrumb-light mb-2">
        <?php $last = count($crumbs) - 1; foreach ($crumbs as $i => $c): ?>
            <?php $label = htmlspecialchars($c['label'] ?? ''); ?>
            <?php if (!empty($c['url']) && $i !== $last): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($c['url']) ?>"><?= $label ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $label ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
