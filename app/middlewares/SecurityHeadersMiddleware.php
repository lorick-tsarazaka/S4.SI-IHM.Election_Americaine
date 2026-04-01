<?php
declare(strict_types=1);

namespace app\middlewares;

use flight\Engine;
use Tracy\Debugger;

class SecurityHeadersMiddleware
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function before(array $params): void
    {
        $nonce = $this->app->get('csp_nonce');

        // Permet l'exécution de la barre de debug Tracy
        $tracyCssBypass = Debugger::$showBar ? "'unsafe-inline'" : "'nonce-{$nonce}'";

        // Content Security Policy propre, sur UNE ligne
        $csp = "default-src 'self'; " .
               "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'; " .
               "style-src 'self' {$tracyCssBypass}; " .
               "img-src 'self' data:; " .
               "font-src 'self'; " .
               "connect-src 'self';";

        // Headers de sécurité
        $this->app->response()->header('X-Frame-Options', 'SAMEORIGIN');
        $this->app->response()->header('Content-Security-Policy', $csp);
        $this->app->response()->header('X-XSS-Protection', '1; mode=block');
        $this->app->response()->header('X-Content-Type-Options', 'nosniff');
        $this->app->response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
        $this->app->response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $this->app->response()->header('Permissions-Policy', 'geolocation=()');
    }
}
