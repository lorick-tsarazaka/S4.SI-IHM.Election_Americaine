<?php $pageTitle = 'Carte des etats'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Carte des etats'],
];
?>
<?php include('inc/header.php'); ?>

<?php
$candidatsBleus = [];
$candidatsRouges = [];

foreach (($candidats ?? []) as $candidat) {
    $nom = (string) ($candidat['nom'] ?? '');
    $couleur = (string) ($candidat['couleur'] ?? '');

    if ($nom === '') {
        continue;
    }

    if ($couleur === 'blue') {
        $candidatsBleus[] = $nom;
    } elseif ($couleur === 'red') {
        $candidatsRouges[] = $nom;
    }
}
?>

<main class="main-content simple-votes-page">
    <div class="result-box">
        <h2 class="mb-3">Carte des etats (vue en carres)</h2>
        <p class="text-muted mb-3">Cliquer sur un etat pour voir le detail des resultats.</p>

        <?php if (!empty($_GET['message'])): ?>
            <p class="saisie-message"><?= htmlspecialchars((string) $_GET['message']) ?></p>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($etats as $etat): ?>
                <?php
                $couleur = (string) ($etat['candidat_couleur'] ?? '');
                $estEgaliteAvecVotes = (int) ($etat['est_egalite_avec_votes'] ?? 0) === 1;

                $cardClass = 'bg-secondary text-white';
                if ($estEgaliteAvecVotes) {
                    $cardClass = 'bg-white text-dark border border-dark';
                } elseif ($couleur === 'blue') {
                    $cardClass = 'bg-primary text-white';
                } elseif ($couleur === 'red') {
                    $cardClass = 'bg-danger text-white';
                }
                ?>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="/detail_etat?etat=<?= (int) $etat['etat_id'] ?>" class="text-decoration-none">
                        <div class="ratio ratio-1x1">
                            <div class="<?= $cardClass ?> rounded d-flex flex-column justify-content-between p-2 shadow-sm">
                                <div>
                                    <strong><?= htmlspecialchars((string) $etat['etat_nom']) ?></strong>
                                </div>
                                <div>
                                    <div class="small">Grand Electeurs: <?= (int) $etat['nb_grands_electeurs'] ?></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-3 small text-muted">
            Bleu: <?= htmlspecialchars(!empty($candidatsBleus) ? implode(', ', $candidatsBleus) : 'candidat(s) bleu') ?> |
            Rouge: <?= htmlspecialchars(!empty($candidatsRouges) ? implode(', ', $candidatsRouges) : 'candidat(s) rouge') ?> |
            Blanc: egalite avec votes |
            Gris: pas de vainqueur
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
