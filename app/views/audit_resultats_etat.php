<?php $pageTitle = 'Audit resultats etat'; ?>
<?php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'Historique resultats etat'],
];
?>
<?php include('inc/header.php'); ?>

<main class="main-content simple-votes-page">
    <div class="result-box">
        <h2 class="mb-3">Historique des modifications des resultats par etat</h2>

        <?php if (!empty($message)): ?>
            <p class="saisie-message"><?= htmlspecialchars((string) $message) ?></p>
        <?php endif; ?>

        <form method="get" action="/audit_resultats_etat" class="row g-2 align-items-end mb-3">
            <div class="col-md-5">
                <label for="etat_id" class="form-label">Filtrer par etat</label>
                <select id="etat_id" name="etat_id" class="form-select">
                    <option value="0">Tous les etats</option>
                    <?php foreach ($etats as $etat): ?>
                        <option value="<?= (int) $etat['id'] ?>" <?= ((int) $etatSelectionne === (int) $etat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $etat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
            <div class="col-md-auto">
                <a class="btn btn-outline-dark" href="/export_historique_resultats_etat_csv<?= (int) $etatSelectionne > 0 ? ('?etat_id=' . (int) $etatSelectionne) : '' ?>">Exporter CSV</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Etat</th>
                        <th>Ancienne valeur</th>
                        <th>Nouvelle valeur</th>
                        <th>Qui</th>
                        <th>Quand</th>
                        <th>Action</th>
                        <th>Rollback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historique)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucune modification de resultat enregistree.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historique as $ligne): ?>
                            <tr>
                                <td><?= (int) $ligne['id'] ?></td>
                                <td><?= htmlspecialchars((string) $ligne['etat_nom']) ?></td>
                                <td><?= htmlspecialchars((string) ($ligne['ancien_candidat_nom'] ?? 'Aucun')) ?></td>
                                <td><?= htmlspecialchars((string) ($ligne['nouveau_candidat_nom'] ?? 'Aucun')) ?></td>
                                <td><?= htmlspecialchars((string) ($ligne['modifie_par_nom_utilisateur'] ?? 'inconnu')) ?></td>
                                <td><?= htmlspecialchars((string) $ligne['date_modification']) ?></td>
                                <td><?= htmlspecialchars((string) $ligne['action_type']) ?></td>
                                <td>
                                    <form method="post" action="/rollback_resultat_etat" onsubmit="return confirm('Confirmer le rollback de cette modification ?');">
                                        <input type="hidden" name="historique_id" value="<?= (int) $ligne['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-warning">Rollback</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
