<?php $pageTitle = 'Tableau des resultats'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Tableau des resultats'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="result-box">
        <table class="result-table">
            <thead>
                <tr>
                    <th>Etat</th>
                    <?php foreach ($candidats as $candidat): ?>
                        <th><?= htmlspecialchars($candidat['nom']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultatsParEtat as $etat): ?>
                    <tr>
                        <td><?= htmlspecialchars($etat['etat_nom']) ?></td>
                        <?php foreach ($candidats as $candidat): ?>
                            <?php $pourcentage = $etat['pourcentages'][(int) $candidat['id']] ?? 0; ?>
                            <td><?= number_format((float) $pourcentage, 2, ',', ' ') ?> %</td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="result-total">
                    <td><strong>Total Grands Electeurs</strong></td>
                    <?php foreach ($candidats as $candidat): ?>
                        <?php $total = $totauxGrandsElecteurs[(int) $candidat['id']] ?? 0; ?>
                        <td><strong><?= (int) $total ?></strong></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <div class="result-link">
            <a href="/resultat_total">Voir resultat</a>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
