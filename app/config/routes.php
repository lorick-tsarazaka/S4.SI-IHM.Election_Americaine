<?php

use app\controllers\MainController;
use app\controllers\TableauBordController;
use app\controllers\DistributionController;
use app\controllers\BesoinController;
use app\controllers\CollecteController;
use app\controllers\SimulationController;
use app\controllers\RecapitulationController;
use app\controllers\AchatsController;
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

//  Tableau de bord Routes
$router->group('', function(Router $router) use ($app) {

    //  Register
    $router->get('/tableauBord', function() use ($app) {
        $controller = new TableauBordController($app);
        $controller->showTableauBord();
    });


    // Saisie Distribution
    $router->get('/distribution/saisie', function() use ($app) {
        $controller = new DistributionController($app);
        $controller->saisieDistribution();
    });

    $router->post('/distribution/saisie', function() use ($app) {
        $controller = new DistributionController($app);
        $controller->enregistrerDistribution();
    });
    // Besoins: saisie + enregistrement
    $router->get('/besoins', function() use ($app) {
        $controller = new BesoinController($app);
        $controller->index();
    });

    $router->post('/besoins/save', function() use ($app) {
        $controller = new BesoinController($app);
        $controller->save();
    });

    // Collecte : liste
    $router->get('/collecte', function() use ($app) {
        $controller = new CollecteController($app);
        $controller->listCollectes();
    });

    // Collecte : formulaire ajout
    $router->get('/collecte/add', function() use ($app) {
        $controller = new CollecteController($app);
        $controller->addCollecte();
    });

    // Collecte : enregistrement
    $router->post('/collecte/add', function() use ($app) {
        $controller = new CollecteController($app);
        $controller->enregistrerCollecte();
    });

    // Collecte : détails (API JSON)
    $router->get('/collecte/details/@id', function($id) use ($app) {
        $controller = new CollecteController($app);
        $controller->getDetails((int) $id);
    });

    // Simulation de dispatch de dons
    $router->get('/simulation', function() use ($app) {
        $controller = new SimulationController($app);
        $controller->index();
    });

    $router->post('/simulation/executer', function() use ($app) {
        $controller = new SimulationController($app);
        $controller->executer();
    });

    $router->post('/simulation/valider', function() use ($app) {
        $controller = new SimulationController($app);
        $controller->valider();
    });

    // Achats - couverture achats
    $router->get('/achats', function() use ($app) {
        $controller = new AchatsController($app);
        $controller->index();
    });

    $router->post('/achats/enregistrer', function() use ($app) {
        $controller = new AchatsController($app);
        $controller->enregistrer();
    });

    // Récapitulation
    $router->get('/recapitulation', function() use ($app) {
        $controller = new RecapitulationController();
        $controller->index();
    });

    $router->get('/recapitulation/data', function() use ($app) {
        $controller = new RecapitulationController();
        $controller->getData();
    });

}, [ SecurityHeadersMiddleware::class ]);