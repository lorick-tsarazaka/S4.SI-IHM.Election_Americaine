<?php $pageTitle = 'Connexion'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Connexion'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="saisie-box">
        <h1 class="saisie-title">Connexion</h1>

        <?php if (!empty($message)): ?>
            <p class="saisie-message"><?= htmlspecialchars((string) $message) ?></p>
        <?php endif; ?>

        <form action="/login_traitement" method="post" class="simple-form">
            <div class="row-candidat">
                <label for="nom_utilisateur">Nom utilisateur</label>
                <input id="nom_utilisateur" name="nom_utilisateur" type="text" value="admin" required>
            </div>

            <div class="row-candidat">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" value="admin123" required>
            </div>

            <div class="row-actions">
                <button type="submit" class="simple-btn">Se connecter</button>
            </div>
        </form>
    </div>
</main>

<?php include('inc/footer.php'); ?>
