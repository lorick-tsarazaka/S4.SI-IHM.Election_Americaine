<?php $base = defined('BASE_URL') ? BASE_URL : ''; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BNGRC - Application de suivi des collectes et distributions de dons pour les sinistrés">
    <meta name="theme-color" content="#2563eb">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Election Americaine</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>/favicon.svg">
    <link rel="apple-touch-icon" href="<?= $base ?>/favicon.svg">
    
    <!-- Styles -->
    <link href="<?= $base ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/styles.css" rel="stylesheet">
    <script src="<?= $base ?>/assets/js/bootstrap/bootstrap.bundle.js" nonce="<?= htmlspecialchars($csp_nonce ?? '') ?>"></script>
</head>