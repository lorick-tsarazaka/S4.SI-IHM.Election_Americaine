<?php

namespace app\repositories;

use PDO;

class AuthRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getUtilisateurByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nom_utilisateur, u.mot_de_passe, r.nom AS role_nom
             FROM utilisateurs u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.nom_utilisateur = ?
             LIMIT 1'
        );
        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user === false ? null : $user;
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $userId]);
    }
}
