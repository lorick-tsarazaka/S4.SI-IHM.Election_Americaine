<?php

use app\controllers\MainController;
use app\controllers\VotesController;
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

    // Saisie et affichage des votes
    $router->get('/saisie_votes', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saisieVotes();
    });

    $router->get('/saisie_votes.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saisieVotes();
    });

    $router->post('/save_votes', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saveVotes();
    });

    $router->post('/save_votes.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->saveVotes();
    });

    $router->get('/tableau_resultats', function() use ($app) {
        $controller = new VotesController($app);
        $controller->tableauResultats();
    });

    $router->get('/tableau_resultats.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->tableauResultats();
    });

    $router->get('/resultat_total', function() use ($app) {
        $controller = new VotesController($app);
        $controller->resultatsTotal();
    });

    $router->get('/resultat_total.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->resultatsTotal();
    });

    $router->get('/generate_pdf', function() use ($app) {
        $controller = new VotesController($app);
        $controller->generatePdf();
    });

    $router->get('/generate_pdf.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->generatePdf();
    });

    $router->get('/detail_candidat', function() use ($app) {
        $controller = new VotesController($app);
        $controller->detailCandidat();
    });

    $router->get('/detail_candidat.php', function() use ($app) {
        $controller = new VotesController($app);
        $controller->detailCandidat();
    });

}, [ SecurityHeadersMiddleware::class ]);