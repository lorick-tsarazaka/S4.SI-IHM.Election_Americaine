<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\repositories\VotesRepository;
use Throwable;

class VotesController
{
    protected Engine $app;
    protected VotesRepository $votesRepository;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->votesRepository = new VotesRepository($app->db());
    }

    private function requireAuthenticated(): bool
    {
        if (!isset($_SESSION['utilisateur'])) {
            Flight::redirect('/login.php?message=Veuillez+vous+connecter');
            return false;
        }

        return true;
    }

    private function requireRole(string $role): bool
    {
        if (!$this->requireAuthenticated()) {
            return false;
        }

        $currentRole = (string) ($_SESSION['utilisateur']['role'] ?? '');
        if ($currentRole !== $role) {
            Flight::redirect('/tableau_resultats.php?message=Acces+refuse');
            return false;
        }

        return true;
    }

    public function saisieVotes(): void
    {
        if (!$this->requireRole('admin')) {
            return;
        }

        $etats = $this->votesRepository->getEtats();
        $candidats = $this->votesRepository->getCandidats();

        Flight::render('saisie_votes', [
            'etats' => $etats,
            'candidats' => $candidats,
            'message' => Flight::request()->query->message,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function saveVotes(): void
    {
        if (!$this->requireRole('admin')) {
            return;
        }

        $request = Flight::request();

        $etatId = (int) ($request->data->etat_id ?? 0);
        $voixParCandidat = $request->data->voix_par_candidat ?? [];

        if (!is_array($voixParCandidat)) {
            $candidatId = (int) ($request->data->candidat_id ?? 0);
            $nombreVoix = (int) ($request->data->nombre_voix ?? 0);

            if ($candidatId > 0) {
                $voixParCandidat = [$candidatId => $nombreVoix];
            } else {
                $voixParCandidat = [];
            }
        }

        if ($etatId <= 0 || empty($voixParCandidat)) {
            Flight::redirect('/saisie_votes?message=Parametres+invalides');
            return;
        }

        $this->app->db()->beginTransaction();

        try {
            $currentUserId = (int) ($_SESSION['utilisateur']['id'] ?? 0);
            $currentUsername = (string) ($_SESSION['utilisateur']['nom_utilisateur'] ?? 'inconnu');

            foreach ($voixParCandidat as $candidatId => $nombreVoix) {
                $candidatId = (int) $candidatId;
                $nombreVoix = (int) $nombreVoix;

                if ($candidatId <= 0 || $nombreVoix < 0) {
                    continue;
                }

                $ancienneValeur = $this->votesRepository->upsertVote($etatId, $candidatId, $nombreVoix);
                $this->votesRepository->addHistoriqueModification($etatId, $candidatId, $ancienneValeur, $nombreVoix);
            }

            $this->votesRepository->updateEtatWinnerWithAudit($etatId, $currentUserId, $currentUsername, 'update');

            $this->app->db()->commit();
            Flight::redirect('/saisie_votes?message=Vote+enregistre');
        } catch (Throwable $e) {
            if ($this->app->db()->inTransaction()) {
                $this->app->db()->rollBack();
            }

            // Log error for debugging
            error_log('SaveVotes Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            Flight::redirect('/saisie_votes?message=Erreur+lors+de+l+enregistrement');
        }
    }

    public function tableauResultats(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $candidats = $this->votesRepository->getCandidats();
        $votesAvecPourcentage = $this->votesRepository->getVotesAvecPourcentage();
        $resultsAvecGrandsElecteurs = $this->votesRepository->getResultatsAvecGrandsElecteurs();

        $resultatsParEtat = [];
        $totauxGrandsElecteurs = [];
        
        foreach ($candidats as $candidat) {
            $totauxGrandsElecteurs[(int) $candidat['id']] = 0;
        }

        // Build data with percentages
        foreach ($votesAvecPourcentage as $ligne) {
            $etatId = (int) $ligne['etat_id'];
            $candidatId = (int) $ligne['candidat_id'];
            $pourcentage = (float) $ligne['pourcentage'];

            if (!isset($resultatsParEtat[$etatId])) {
                $resultatsParEtat[$etatId] = [
                    'etat_nom' => $ligne['etat_nom'],
                    'pourcentages' => [],
                ];
            }

            $resultatsParEtat[$etatId]['pourcentages'][$candidatId] = $pourcentage;
        }

        // Add grands electeurs totals
        foreach ($resultsAvecGrandsElecteurs as $ligne) {
            $candidatId = (int) $ligne['candidat_id'];
            $grandsElecteurs = (int) $ligne['grands_electeurs_remportes'];
            $totauxGrandsElecteurs[$candidatId] += $grandsElecteurs;
        }

        Flight::render('tableau_resultats', [
            'candidats' => $candidats,
            'resultatsParEtat' => $resultatsParEtat,
            'totauxGrandsElecteurs' => $totauxGrandsElecteurs,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function resultatsTotal(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $resultatsTotal = $this->votesRepository->getResultatsTotal();

        Flight::render('resultat_total', [
            'resultatsTotal' => $resultatsTotal,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function generatePdf(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $resultatsTotal = $this->votesRepository->getResultatsTotal();

        Flight::render('generate_pdf', [
            'resultatsTotal' => $resultatsTotal,
        ]);
    }

    public function detailCandidat(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $candidatId = (int) Flight::request()->query->candidat ?? 0;

        if ($candidatId <= 0) {
            Flight::redirect('/');
            return;
        }

        $candidat = $this->votesRepository->getDetailCandidat($candidatId);
        if ($candidat === null) {
            Flight::notFound();
            return;
        }

        $resultatsParticulier = $this->votesRepository->getResultatsParticulierCandidat($candidatId);

        Flight::render('detail_candidat', [
            'candidat' => $candidat,
            'resultatsParticulier' => $resultatsParticulier,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function carte(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $etats = $this->votesRepository->getEtatsAvecGagnantPourCarte();
        $candidats = $this->votesRepository->getCandidats();

        Flight::render('carte', [
            'etats' => $etats,
            'candidats' => $candidats,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function detailEtat(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $etatId = (int) (Flight::request()->query->etat ?? 0);
        if ($etatId <= 0) {
            Flight::redirect('/carte');
            return;
        }

        $etat = $this->votesRepository->getEtatById($etatId);
        if ($etat === null) {
            Flight::notFound();
            return;
        }

        $votesEtat = $this->votesRepository->getDetailVotesEtat($etatId);

        Flight::render('detail_etat', [
            'etat' => $etat,
            'votesEtat' => $votesEtat,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function totalElecteurs(): void
    {
        if (!$this->requireAuthenticated()) {
            return;
        }

        $totaux = $this->votesRepository->getTotalElecteursParCandidat();

        Flight::render('total_electeurs', [
            'totaux' => $totaux,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function auditResultatsEtat(): void
    {
        if (!$this->requireRole('admin')) {
            return;
        }

        $etatId = (int) (Flight::request()->query->etat_id ?? 0);
        $etatFilter = $etatId > 0 ? $etatId : null;

        $etats = $this->votesRepository->getEtats();
        $historique = $this->votesRepository->getHistoriqueResultatsEtat($etatFilter);

        Flight::render('audit_resultats_etat', [
            'etats' => $etats,
            'historique' => $historique,
            'etatSelectionne' => $etatId,
            'message' => Flight::request()->query->message,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function rollbackResultatEtat(): void
    {
        if (!$this->requireRole('admin')) {
            return;
        }

        $historiqueId = (int) (Flight::request()->data->historique_id ?? 0);
        if ($historiqueId <= 0) {
            Flight::redirect('/audit_resultats_etat?message=Historique+invalide');
            return;
        }

        $currentUserId = (int) ($_SESSION['utilisateur']['id'] ?? 0);
        $currentUsername = (string) ($_SESSION['utilisateur']['nom_utilisateur'] ?? 'inconnu');

        $this->app->db()->beginTransaction();
        try {
            $ok = $this->votesRepository->rollbackHistoriqueResultatEtat($historiqueId, $currentUserId, $currentUsername);
            if (!$ok) {
                $this->app->db()->rollBack();
                Flight::redirect('/audit_resultats_etat?message=Historique+introuvable');
                return;
            }

            $this->app->db()->commit();
            Flight::redirect('/audit_resultats_etat?message=Rollback+effectue');
        } catch (Throwable $e) {
            if ($this->app->db()->inTransaction()) {
                $this->app->db()->rollBack();
            }

            error_log('RollbackResultatEtat Error: ' . $e->getMessage());
            Flight::redirect('/audit_resultats_etat?message=Erreur+lors+du+rollback');
        }
    }

    public function exportHistoriqueResultatsEtatCsv(): void
    {
        if (!$this->requireRole('admin')) {
            return;
        }

        $etatId = (int) (Flight::request()->query->etat_id ?? 0);
        $etatFilter = $etatId > 0 ? $etatId : null;
        $historique = $this->votesRepository->getHistoriqueResultatsEtat($etatFilter);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_resultats_etat.csv"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            return;
        }

        fputcsv($output, [
            'id',
            'etat',
            'ancienne_valeur',
            'nouvelle_valeur',
            'modifie_par',
            'action',
            'date_modification',
        ]);

        foreach ($historique as $ligne) {
            fputcsv($output, [
                (int) $ligne['id'],
                (string) $ligne['etat_nom'],
                (string) ($ligne['ancien_candidat_nom'] ?? 'Aucun'),
                (string) ($ligne['nouveau_candidat_nom'] ?? 'Aucun'),
                (string) ($ligne['modifie_par_nom_utilisateur'] ?? 'inconnu'),
                (string) $ligne['action_type'],
                (string) $ligne['date_modification'],
            ]);
        }

        fclose($output);
        exit;
    }
}
