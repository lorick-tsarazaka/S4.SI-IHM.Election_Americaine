<?php
    // Ensure $base is available even if BASE_URL is not defined
    $base = defined('BASE_URL') ? BASE_URL : '';
    $currentPage = $pageTitle ?? ($page_title ?? '');
    include ("doctype.php");
?>
<body>
    <!-- Navbar moderne -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-gradient fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/">
                <div class="brand-icon me-2">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <div class="brand-text">
                    <span class="fw-bold">Election Americaine</span>
                </div>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3 <?= $currentPage === 'Tableau de bord' ? 'active' : '' ?>" href="<?= $base ?>/">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3 <?= $currentPage === 'Saisie des votes' ? 'active' : '' ?>" href="<?= $base ?>/saisie_votes">
                            <i class="bi bi-pencil-square me-1"></i> Saisie votes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3 <?= $currentPage === 'Tableau des resultats' ? 'active' : '' ?>" href="<?= $base ?>/tableau_resultats">
                            <i class="bi bi-table me-1"></i> Resultats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3" href="<?= $base ?>/generate_pdf">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Spacer pour le fixed navbar -->
    <div class="navbar-spacer"></div>

    <!-- Breadcrumb moved into each page's hero section to keep it inside the hero -->