<?php

namespace app\repositories;

use PDO;

class VotesRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getEtats(): array
    {
        $stmt = $this->db->query('SELECT id, nom FROM etats ORDER BY nom ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCandidats(): array
    {
        $stmt = $this->db->query('SELECT id, nom FROM candidats ORDER BY nom ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVote(int $etatId, int $candidatId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre_voix FROM votes WHERE etat_id = ? AND candidat_id = ? LIMIT 1');
        $stmt->execute([$etatId, $candidatId]);
        $vote = $stmt->fetch(PDO::FETCH_ASSOC);

        return $vote === false ? null : $vote;
    }

    public function upsertVote(int $etatId, int $candidatId, int $nombreVoix): int
    {
        $existing = $this->getVote($etatId, $candidatId);
        $anciennesVoix = $existing !== null ? (int) $existing['nombre_voix'] : 0;

        if ($existing !== null) {
            $stmt = $this->db->prepare('UPDATE votes SET nombre_voix = ? WHERE id = ?');
            $stmt->execute([$nombreVoix, $existing['id']]);
            return $anciennesVoix;
        }

        $stmt = $this->db->prepare('INSERT INTO votes (etat_id, candidat_id, nombre_voix) VALUES (?, ?, ?)');
        $stmt->execute([$etatId, $candidatId, $nombreVoix]);

        return $anciennesVoix;
    }

    public function updateEtatWinner(int $etatId): void
    {
        $this->updateEtatWinnerWithAudit($etatId, null, null, 'update');
    }

    public function updateEtatWinnerWithAudit(
        int $etatId,
        ?int $userId,
        ?string $username,
        string $actionType = 'update'
    ): void {
        $maxStmt = $this->db->prepare(
            'SELECT MAX(nombre_voix) AS max_voix
             FROM votes
             WHERE etat_id = ?'
        );
        $maxStmt->execute([$etatId]);
        $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);

        if ($maxResult === false || $maxResult['max_voix'] === null) {
            return;
        }

        $maxVoix = (int) $maxResult['max_voix'];

        $tieStmt = $this->db->prepare(
            'SELECT COUNT(*) AS nb_candidats_max
             FROM votes
             WHERE etat_id = ? AND nombre_voix = ?'
        );
        $tieStmt->execute([$etatId, $maxVoix]);
        $tieResult = $tieStmt->fetch(PDO::FETCH_ASSOC);
        $nbCandidatsMax = (int) ($tieResult['nb_candidats_max'] ?? 0);

        $winnerId = null;
        if ($nbCandidatsMax === 1) {
            $winnerStmt = $this->db->prepare(
                'SELECT candidat_id
                 FROM votes
                 WHERE etat_id = ? AND nombre_voix = ?
                 LIMIT 1'
            );
            $winnerStmt->execute([$etatId, $maxVoix]);
            $winner = $winnerStmt->fetch(PDO::FETCH_ASSOC);
            if ($winner !== false) {
                $winnerId = (int) $winner['candidat_id'];
            }
        }

        $ancienWinnerId = $this->getEtatWinnerId($etatId);
        if ($ancienWinnerId === $winnerId) {
            return;
        }

        $checkStmt = $this->db->prepare('SELECT id FROM resultats_etat WHERE etat_id = ?');
        $checkStmt->execute([$etatId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing !== false) {
            $updateStmt = $this->db->prepare('UPDATE resultats_etat SET candidat_gagnant_id = ? WHERE etat_id = ?');
            $updateStmt->execute([$winnerId, $etatId]);
        } else {
            $insertStmt = $this->db->prepare('INSERT INTO resultats_etat (etat_id, candidat_gagnant_id) VALUES (?, ?)');
            $insertStmt->execute([$etatId, $winnerId]);
        }

        $this->addHistoriqueResultatEtat(
            $etatId,
            $ancienWinnerId,
            $winnerId,
            $userId,
            $username,
            $actionType
        );
    }

    private function getEtatWinnerId(int $etatId): ?int
    {
        $stmt = $this->db->prepare('SELECT candidat_gagnant_id FROM resultats_etat WHERE etat_id = ? LIMIT 1');
        $stmt->execute([$etatId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false || $row['candidat_gagnant_id'] === null) {
            return null;
        }

        return (int) $row['candidat_gagnant_id'];
    }

    public function addHistoriqueResultatEtat(
        int $etatId,
        ?int $ancienCandidatId,
        ?int $nouveauCandidatId,
        ?int $userId,
        ?string $username,
        string $actionType = 'update'
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO historique_resultats_etat (
                etat_id,
                ancien_candidat_gagnant_id,
                nouveau_candidat_gagnant_id,
                modifie_par_utilisateur_id,
                modifie_par_nom_utilisateur,
                action_type
            ) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $etatId,
            $ancienCandidatId,
            $nouveauCandidatId,
            $userId,
            $username,
            $actionType,
        ]);
    }

    public function addHistoriqueModification(
        int $etatId,
        int $candidatId,
        int $anciennesVoix,
        int $nouvellesVoix
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO historique_modifications (etat_id, candidat_id, anciennes_voix, nouvelles_voix)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$etatId, $candidatId, $anciennesVoix, $nouvellesVoix]);
    }

    public function getVotesAvecPourcentage(): array
    {
        $stmt = $this->db->query(
            'SELECT
                e.id AS etat_id,
                e.nom AS etat_nom,
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                COALESCE(v.nombre_voix, 0) AS nombre_voix,
                CASE
                    WHEN totals.total_voix > 0 THEN ROUND((COALESCE(v.nombre_voix, 0) / totals.total_voix) * 100, 2)
                    ELSE 0
                END AS pourcentage
            FROM etats e
            CROSS JOIN candidats c
            LEFT JOIN votes v ON v.etat_id = e.id AND v.candidat_id = c.id
            LEFT JOIN (
                SELECT etat_id, SUM(nombre_voix) AS total_voix
                FROM votes
                GROUP BY etat_id
            ) totals ON totals.etat_id = e.id
            ORDER BY e.nom ASC, c.nom ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResultatsAvecGrandsElecteurs(): array
    {
        $stmt = $this->db->query(
            'SELECT
                e.id AS etat_id,
                e.nom AS etat_nom,
                e.nb_grands_electeurs,
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                CASE
                    WHEN re.candidat_gagnant_id = c.id THEN e.nb_grands_electeurs
                    ELSE 0
                END AS grands_electeurs_remportes
            FROM etats e
            CROSS JOIN candidats c
            LEFT JOIN resultats_etat re ON re.etat_id = e.id
            ORDER BY e.nom ASC, c.nom ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResultatsTotal(): array
    {
        $stmt = $this->db->query(
            'SELECT
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                SUM(COALESCE(v.nombre_voix, 0)) AS total_voix,
                ROUND((SUM(COALESCE(v.nombre_voix, 0)) / 
                    (SELECT SUM(nombre_voix) FROM votes)) * 100, 2) AS pourcentage_total,
                COUNT(DISTINCT re.etat_id) AS etats_remportes,
                COALESCE(ge.total_grands_electeurs, 0) AS total_grands_electeurs
            FROM candidats c
            LEFT JOIN votes v ON v.candidat_id = c.id
            LEFT JOIN resultats_etat re ON re.candidat_gagnant_id = c.id
            LEFT JOIN (
                SELECT 
                    re.candidat_gagnant_id,
                    SUM(e.nb_grands_electeurs) AS total_grands_electeurs
                FROM resultats_etat re
                LEFT JOIN etats e ON e.id = re.etat_id
                GROUP BY re.candidat_gagnant_id
            ) ge ON ge.candidat_gagnant_id = c.id
            GROUP BY c.id, c.nom, c.couleur, ge.total_grands_electeurs
            ORDER BY total_grands_electeurs DESC, etats_remportes DESC, total_voix DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailCandidat(int $candidatId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, couleur FROM candidats WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$candidatId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getResultatsParticulierCandidat(int $candidatId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                e.id AS etat_id,
                e.nom AS etat_nom,
                e.nb_grands_electeurs,
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                COALESCE(v.nombre_voix, 0) AS nombre_voix,
                CASE
                    WHEN totals.total_voix > 0 THEN ROUND((COALESCE(v.nombre_voix, 0) / totals.total_voix) * 100, 2)
                    ELSE 0
                END AS pourcentage,
                CASE
                    WHEN re.candidat_gagnant_id = c.id THEN e.nb_grands_electeurs
                    ELSE 0
                END AS grands_electeurs_remportes
            FROM etats e
            CROSS JOIN candidats c
            LEFT JOIN votes v ON v.etat_id = e.id AND v.candidat_id = c.id
            LEFT JOIN resultats_etat re ON re.etat_id = e.id
            LEFT JOIN (
                SELECT etat_id, SUM(nombre_voix) AS total_voix
                FROM votes
                GROUP BY etat_id
            ) totals ON totals.etat_id = e.id
            WHERE c.id = ?
            ORDER BY e.nom ASC'
        );
        $stmt->execute([$candidatId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEtatsAvecGagnantPourCarte(): array
    {
        $stmt = $this->db->query(
            'SELECT
                e.id AS etat_id,
                e.nom AS etat_nom,
                e.nb_grands_electeurs,
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                COALESCE((
                    SELECT SUM(vs.nombre_voix)
                    FROM votes vs
                    WHERE vs.etat_id = e.id
                ), 0) AS total_voix,
                CASE
                    WHEN COALESCE((
                        SELECT SUM(vs.nombre_voix)
                        FROM votes vs
                        WHERE vs.etat_id = e.id
                    ), 0) > 0
                    AND (
                        SELECT COUNT(*)
                        FROM votes vm
                        WHERE vm.etat_id = e.id
                          AND vm.nombre_voix = (
                              SELECT MAX(vx.nombre_voix)
                              FROM votes vx
                              WHERE vx.etat_id = e.id
                          )
                    ) > 1
                    THEN 1
                    ELSE 0
                END AS est_egalite_avec_votes
            FROM etats e
            LEFT JOIN resultats_etat re ON re.etat_id = e.id
            LEFT JOIN candidats c ON c.id = re.candidat_gagnant_id
            ORDER BY e.nom ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEtatById(int $etatId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nom, nb_grands_electeurs FROM etats WHERE id = ? LIMIT 1');
        $stmt->execute([$etatId]);

        $etat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $etat === false ? null : $etat;
    }

    public function getDetailVotesEtat(int $etatId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                COALESCE(v.nombre_voix, 0) AS nombre_voix,
                CASE
                    WHEN totals.total_voix > 0 THEN ROUND((COALESCE(v.nombre_voix, 0) / totals.total_voix) * 100, 2)
                    ELSE 0
                END AS pourcentage,
                CASE
                    WHEN re.candidat_gagnant_id = c.id THEN 1
                    ELSE 0
                END AS est_gagnant
            FROM candidats c
            LEFT JOIN votes v ON v.candidat_id = c.id AND v.etat_id = ?
            LEFT JOIN resultats_etat re ON re.etat_id = ?
            LEFT JOIN (
                SELECT etat_id, SUM(nombre_voix) AS total_voix
                FROM votes
                WHERE etat_id = ?
                GROUP BY etat_id
            ) totals ON totals.etat_id = ?
            ORDER BY c.nom ASC'
        );
        $stmt->execute([$etatId, $etatId, $etatId, $etatId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recalculerResultatsEtat(): int
    {
        $stmt = $this->db->query('SELECT id FROM etats ORDER BY id ASC');
        $etats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($etats as $etat) {
            $this->updateEtatWinnerWithAudit((int) $etat['id'], null, null, 'recalcul');
        }

        return count($etats);
    }

    public function getHistoriqueResultatsEtat(?int $etatId = null): array
    {
        if ($etatId !== null && $etatId > 0) {
            $stmt = $this->db->prepare(
                'SELECT
                    h.id,
                    h.etat_id,
                    e.nom AS etat_nom,
                    h.ancien_candidat_gagnant_id,
                    h.nouveau_candidat_gagnant_id,
                    ca.nom AS ancien_candidat_nom,
                    cn.nom AS nouveau_candidat_nom,
                    h.modifie_par_utilisateur_id,
                    h.modifie_par_nom_utilisateur,
                    h.action_type,
                    h.date_modification
                FROM historique_resultats_etat h
                INNER JOIN etats e ON e.id = h.etat_id
                LEFT JOIN candidats ca ON ca.id = h.ancien_candidat_gagnant_id
                LEFT JOIN candidats cn ON cn.id = h.nouveau_candidat_gagnant_id
                WHERE h.etat_id = ?
                ORDER BY h.date_modification DESC, h.id DESC'
            );
            $stmt->execute([$etatId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->db->query(
            'SELECT
                h.id,
                h.etat_id,
                e.nom AS etat_nom,
                h.ancien_candidat_gagnant_id,
                h.nouveau_candidat_gagnant_id,
                ca.nom AS ancien_candidat_nom,
                cn.nom AS nouveau_candidat_nom,
                h.modifie_par_utilisateur_id,
                h.modifie_par_nom_utilisateur,
                h.action_type,
                h.date_modification
            FROM historique_resultats_etat h
            INNER JOIN etats e ON e.id = h.etat_id
            LEFT JOIN candidats ca ON ca.id = h.ancien_candidat_gagnant_id
            LEFT JOIN candidats cn ON cn.id = h.nouveau_candidat_gagnant_id
            ORDER BY h.date_modification DESC, h.id DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoriqueResultatEtatById(int $historiqueId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, etat_id, ancien_candidat_gagnant_id, nouveau_candidat_gagnant_id
             FROM historique_resultats_etat
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$historiqueId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function rollbackHistoriqueResultatEtat(int $historiqueId, int $userId, string $username): bool
    {
        $historique = $this->getHistoriqueResultatEtatById($historiqueId);
        if ($historique === null) {
            return false;
        }

        $etatId = (int) $historique['etat_id'];
        $winnerActuel = $this->getEtatWinnerId($etatId);
        $winnerRollback = $historique['ancien_candidat_gagnant_id'] !== null
            ? (int) $historique['ancien_candidat_gagnant_id']
            : null;

        if ($winnerActuel === $winnerRollback) {
            return true;
        }

        $existsStmt = $this->db->prepare('SELECT id FROM resultats_etat WHERE etat_id = ? LIMIT 1');
        $existsStmt->execute([$etatId]);
        $exists = $existsStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists !== false) {
            $updateStmt = $this->db->prepare('UPDATE resultats_etat SET candidat_gagnant_id = ? WHERE etat_id = ?');
            $updateStmt->execute([$winnerRollback, $etatId]);
        } else {
            $insertStmt = $this->db->prepare('INSERT INTO resultats_etat (etat_id, candidat_gagnant_id) VALUES (?, ?)');
            $insertStmt->execute([$etatId, $winnerRollback]);
        }

        $this->addHistoriqueResultatEtat(
            $etatId,
            $winnerActuel,
            $winnerRollback,
            $userId,
            $username,
            'rollback'
        );

        return true;
    }

    public function getTotalElecteursParCandidat(): array
    {
        $stmt = $this->db->query(
            'SELECT
                c.id AS candidat_id,
                c.nom AS candidat_nom,
                c.couleur AS candidat_couleur,
                COALESCE(SUM(e.nb_grands_electeurs), 0) AS total_grands_electeurs,
                COUNT(DISTINCT re.etat_id) AS etats_remportes
            FROM candidats c
            LEFT JOIN resultats_etat re ON re.candidat_gagnant_id = c.id
            LEFT JOIN etats e ON e.id = re.etat_id
            GROUP BY c.id, c.nom, c.couleur
            ORDER BY total_grands_electeurs DESC, etats_remportes DESC, c.nom ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
