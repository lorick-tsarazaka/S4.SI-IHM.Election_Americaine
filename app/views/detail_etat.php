<?php $pageTitle = 'Detail etat'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Carte des etats', 'url' => '/carte'],
    ['label' => 'Detail etat'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="result-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Detail - <?= htmlspecialchars((string) $etat['nom']) ?></h2>
            <a href="/carte" class="btn btn-outline-dark">Retour carte</a>
        </div>

        <p><strong>Grands electeurs:</strong> <?= (int) $etat['nb_grands_electeurs'] ?></p>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Votes</th>
                    <th>Pourcentage</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($votesEtat as $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $ligne['candidat_nom']) ?></td>
                        <td><?= (int) $ligne['nombre_voix'] ?></td>
                        <td><?= number_format((float) $ligne['pourcentage'], 2, ',', ' ') ?> %</td>
                        <td><?= (int) $ligne['est_gagnant'] === 1 ? 'Gagnant' : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include('inc/footer.php'); ?>
