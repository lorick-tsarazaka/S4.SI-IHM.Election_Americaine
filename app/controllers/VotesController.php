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

    public function saisieVotes(): void
    {
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
            foreach ($voixParCandidat as $candidatId => $nombreVoix) {
                $candidatId = (int) $candidatId;
                $nombreVoix = (int) $nombreVoix;

                if ($candidatId <= 0 || $nombreVoix < 0) {
                    continue;
                }

                $ancienneValeur = $this->votesRepository->upsertVote($etatId, $candidatId, $nombreVoix);
                $this->votesRepository->addHistoriqueModification($etatId, $candidatId, $ancienneValeur, $nombreVoix);
            }

            $this->votesRepository->updateEtatWinner($etatId);

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
        $resultatsTotal = $this->votesRepository->getResultatsTotal();

        Flight::render('resultat_total', [
            'resultatsTotal' => $resultatsTotal,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function generatePdf(): void
    {
        $resultatsTotal = $this->votesRepository->getResultatsTotal();

        Flight::render('generate_pdf', [
            'resultatsTotal' => $resultatsTotal,
        ]);
    }

    public function detailCandidat(): void
    {
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
}
