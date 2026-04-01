<?php $pageTitle = 'Saisie des votes'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Saisie des votes'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="saisie-box">
        <h1 class="saisie-title">Saisie Resultat</h1>

        <?php if (!empty($message)): ?>
            <p class="saisie-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="/save_votes" method="post" class="simple-form">
            <div class="row-etat">
                <select id="etat_id" name="etat_id" class="simple-select" required>
                    <option value="" selected disabled>Choisir Etat</option>
                    <?php foreach ($etats as $etat): ?>
                        <option value="<?= (int) $etat['id'] ?>"><?= htmlspecialchars($etat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ($candidats as $candidat): ?>
                <div class="row-candidat">
                    <label for="cand-<?= (int) $candidat['id'] ?>"><?= htmlspecialchars($candidat['nom']) ?></label>
                    <input
                        id="cand-<?= (int) $candidat['id'] ?>"
                        name="voix_par_candidat[<?= (int) $candidat['id'] ?>]"
                        type="number"
                        min="0"
                        step="1"
                        value="0"
                        required
                    >
                </div>
            <?php endforeach; ?>

            <div class="row-actions">
                <button type="submit" class="simple-btn">Valider</button>
            </div>
        </form>
    </div>
</main>

<?php include('inc/footer.php'); ?>
