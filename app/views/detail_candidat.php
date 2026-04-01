<?php $pageTitle = 'Détail Candidat - ' . htmlspecialchars($candidat['nom']); ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Tableau des résultats', 'url' => '/tableau_resultats'],
    ['label' => 'Résultats Totaux', 'url' => '/resultat_total'],
    ['label' => 'Détail - ' . htmlspecialchars($candidat['nom'])],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content detail-candidat-page">
    <div class="result-box">
        <h2>Détail - <?= htmlspecialchars($candidat['nom']) ?></h2>
        
        <div class="candidat-summary">
            <div class="candidat-color" style="background-color: <?= htmlspecialchars($candidat['couleur']) ?>"></div>
            <div class="candidat-info">
                <p><strong>Candidat:</strong> <?= htmlspecialchars($candidat['nom']) ?></p>
            </div>
        </div>

        <table class="result-table">
            <thead>
                <tr>
                    <th>État</th>
                    <th>Nb Grand électeur</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGrandsElecteurs = 0;
                foreach ($resultatsParticulier as $resultat): 
                    $totalGrandsElecteurs += (int) $resultat['grands_electeurs_remportes'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($resultat['etat_nom']) ?></td>
                        <td><?= (int) $resultat['grands_electeurs_remportes'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="result-total">
                    <td><strong>Total</strong></td>
                    <td><strong><?= $totalGrandsElecteurs ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="result-link">
            <a href="/resultat_total" class="btn">Retour aux résultats</a>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
