<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\repositories\VilleRepository;
use app\repositories\BesoinRepository;
use app\repositories\DistributionRepository;
use app\services\TableauBordService;

class MainController {
    protected Engine $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * Page d'accueil - Tableau de bord
     */
    public function home() {
        Flight::render('home', [
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

}