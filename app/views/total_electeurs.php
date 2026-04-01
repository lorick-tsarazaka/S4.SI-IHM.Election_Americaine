<?php $pageTitle = 'Total electeurs'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Total electeurs'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="result-box">
        <h2>Total des grands electeurs par candidat</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Etats remportes</th>
                    <th>Total grands electeurs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totaux as $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $ligne['candidat_nom']) ?></td>
                        <td><?= (int) $ligne['etats_remportes'] ?></td>
                        <td><strong><?= (int) $ligne['total_grands_electeurs'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="/carte" class="btn btn-outline-dark">Retour carte</a>
    </div>
</main>

<?php include('inc/footer.php'); ?>
