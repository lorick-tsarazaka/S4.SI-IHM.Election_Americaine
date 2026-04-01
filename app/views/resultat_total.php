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
                    $compareGe = (int) $b['total_grands_electeurs'] - (int) $a['total_grands_electeurs'];
                    if ($compareGe !== 0) {
                        return $compareGe;
                    }

                    $compareEtats = (int) $b['etats_remportes'] - (int) $a['etats_remportes'];
                    if ($compareEtats !== 0) {
                        return $compareEtats;
                    }

                    return (int) $b['total_voix'] - (int) $a['total_voix'];
                });
                $candidatGagnant = 'N/A';
                if (!empty($resultatsTotal) && (int) $resultatsTotal[0]['total_grands_electeurs'] > 0) {
                    $candidatGagnant = (string) $resultatsTotal[0]['candidat_nom'];

                    if (count($resultatsTotal) > 1) {
                        $premier = $resultatsTotal[0];
                        $second = $resultatsTotal[1];

                        if ((int) $premier['total_grands_electeurs'] === (int) $second['total_grands_electeurs']
                            && (int) $premier['etats_remportes'] === (int) $second['etats_remportes']) {
                            $candidatGagnant = 'Egalite';
                        }
                    }
                }

                foreach ($resultatsTotal as $resultat): 
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
