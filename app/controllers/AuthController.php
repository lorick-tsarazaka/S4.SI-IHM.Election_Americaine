<?php

namespace app\controllers;

use Flight;
use flight\Engine;
use app\repositories\AuthRepository;

class AuthController
{
    protected Engine $app;
    protected AuthRepository $authRepository;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->authRepository = new AuthRepository($app->db());
    }

    public function login(): void
    {
        if (isset($_SESSION['utilisateur'])) {
            $role = (string) ($_SESSION['utilisateur']['role'] ?? '');
            if ($role === 'admin') {
                Flight::redirect('/saisie_votes');
                return;
            }

            Flight::redirect('/tableau_resultats');
            return;
        }

        Flight::render('login', [
            'message' => Flight::request()->query->message,
            'csp_nonce' => Flight::get('csp_nonce'),
        ]);
    }

    public function loginTraitement(): void
    {
        $request = Flight::request();
        $username = trim((string) ($request->data->nom_utilisateur ?? ''));
        $password = (string) ($request->data->mot_de_passe ?? '');

        if ($username === '' || $password === '') {
            Flight::redirect('/login?message=Identifiants+obligatoires');
            return;
        }

        $user = $this->authRepository->getUtilisateurByUsername($username);
        if ($user === null || !password_verify($password, (string) $user['mot_de_passe'])) {
            Flight::redirect('/login?message=Nom+utilisateur+ou+mot+de+passe+incorrect');
            return;
        }

        if (password_needs_rehash((string) $user['mot_de_passe'], PASSWORD_DEFAULT)) {
            $this->authRepository->updatePasswordHash((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        $_SESSION['utilisateur'] = [
            'id' => (int) $user['id'],
            'nom_utilisateur' => (string) $user['nom_utilisateur'],
            'role' => (string) $user['role_nom'],
        ];

        if ((string) $user['role_nom'] === 'observateur' || (string) $user['role_nom'] === 'admin') {
            Flight::redirect('/');
            return;
        }

        Flight::redirect('/login?message=Role+inconnu');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        Flight::redirect('/login?message=Deconnexion+reussie');
    }
}
