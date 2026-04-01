<?php $pageTitle = 'Résultat'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Tableau des résultats', 'url' => '/tableau_resultats'],
    ['label' => 'Résultat'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content resultat-total-page">
    <div class="result-box">
        <h2>Résultat</h2>
        <div class="result-link">
            <a href="/generate_pdf" class="btn">Exporter en PDF</a>
        </div>
        <table class="result-table">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Nb Grand électeur</th>
                    <th class="action-column">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                usort($resultatsTotal, function($a, $b) {
                    return (int) $b['total_grands_electeurs'] - (int) $a['total_grands_electeurs'];
                });
                $candidatGagnant = null;
                foreach ($resultatsTotal as $resultat): 
                    if ($candidatGagnant === null && (int) $resultat['total_grands_electeurs'] > 0) {
                        $candidatGagnant = $resultat['candidat_nom'];
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($resultat['candidat_nom']) ?></td>
                        <td><?= (int) $resultat['total_grands_electeurs'] ?></td>
                        <td class="action-column">
                            <a href="/detail_candidat?candidat=<?= (int) $resultat['candidat_id'] ?>" class="btn btn-small">Détail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="winner-section">
            <p><strong>Vainqueur : <?= htmlspecialchars($candidatGagnant ?? 'N/A') ?></strong></p>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
