<?php

use app\controllers\MainController;
use app\controllers\VotesController;
use app\controllers\AuthController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

$router->group('', function(Router $router) use ($app) {
    // Page d'accueil - Tableau de bord
    $router->get('/', function() use ($app) {
        $controller = new MainController($app);
        $controller->home();
    });
}, [ SecurityHeadersMiddleware::class ]);

$router->group('', function(Router $router) use ($app) {
    $router->get('/login', function() use ($app) {
        $controller = new AuthController($app);
        $controller->login();
    });

    $router->post('/login_traitement', function() use ($app) {
        $controller = new AuthController($app);
        $controller->loginTraitement();
    });

    $router->get('/logout', function() use ($app) {
        $controller = new AuthController($app);
        $controller->logout();
    });

    // Saisie et affichage des votes
    $router->get('/saisie_votes', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saisieVotes();
    });

    $router->post('/save_votes', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saveVotes();
    });

    $router->get('/tableau_resultats', function() use ($app) {
        $controller = new VotesController($app);
        $controller->tableauResultats();
    });

    $router->get('/resultat_total', function() use ($app) {
        $controller = new VotesController($app);
        $controller->resultatsTotal();
    });

    $router->get('/generate_pdf', function() use ($app) {
        $controller = new VotesController($app);
        $controller->generatePdf();
    });

    $router->get('/detail_candidat', function() use ($app) {
        $controller = new VotesController($app);
        $controller->detailCandidat();
    });

    $router->get('/carte', function() use ($app) {
        $controller = new VotesController($app);
        $controller->carte();
    });

    $router->get('/detail_etat', function() use ($app) {
        $controller = new VotesController($app);
        $controller->detailEtat();
    });

    $router->get('/total_electeurs', function() use ($app) {
        $controller = new VotesController($app);
        $controller->totalElecteurs();
    });

    $router->get('/audit_resultats_etat', function() use ($app) {
        $controller = new VotesController($app);
        $controller->auditResultatsEtat();
    });

    $router->post('/rollback_resultat_etat', function() use ($app) {
        $controller = new VotesController($app);
        $controller->rollbackResultatEtat();
    });

    $router->get('/export_historique_resultats_etat_csv', function() use ($app) {
        $controller = new VotesController($app);
        $controller->exportHistoriqueResultatsEtatCsv();
    });

}, [ SecurityHeadersMiddleware::class ]);