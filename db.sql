-- election_db.sql
DROP DATABASE IF EXISTS election_db;
CREATE DATABASE election_db;
USE election_db;

CREATE TABLE etats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    nb_grands_electeurs INT NOT NULL
);

CREATE TABLE candidats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    couleur VARCHAR(20) -- bleu / rouge
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etat_id INT,
    candidat_id INT,
    nombre_voix INT DEFAULT 0,
    FOREIGN KEY (etat_id) REFERENCES etats(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE,
    UNIQUE(etat_id, candidat_id)
);

CREATE TABLE resultats_etat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etat_id INT UNIQUE,
    candidat_gagnant_id INT,
    FOREIGN KEY (etat_id) REFERENCES etats(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_gagnant_id) REFERENCES candidats(id)
);

CREATE TABLE historique_modifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etat_id INT,
    candidat_id INT,
    anciennes_voix INT,
    nouvelles_voix INT,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etat_id) REFERENCES etats(id),
    FOREIGN KEY (candidat_id) REFERENCES candidats(id)
);

CREATE TABLE historique_resultats_etat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etat_id INT NOT NULL,
    ancien_candidat_gagnant_id INT NULL,
    nouveau_candidat_gagnant_id INT NULL,
    modifie_par_utilisateur_id INT NULL,
    modifie_par_nom_utilisateur VARCHAR(100) NULL,
    action_type VARCHAR(30) NOT NULL DEFAULT 'update',
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etat_id) REFERENCES etats(id) ON DELETE CASCADE,
    FOREIGN KEY (ancien_candidat_gagnant_id) REFERENCES candidats(id) ON DELETE SET NULL,
    FOREIGN KEY (nouveau_candidat_gagnant_id) REFERENCES candidats(id) ON DELETE SET NULL,
    FOREIGN KEY (modifie_par_utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);



-- ETATS (50 états américains + District de Columbia)
INSERT INTO etats (nom, nb_grands_electeurs) VALUES
('Alabama', 9),
('Alaska', 3),
('Arizona', 11),
('Arkansas', 6),
('Californie', 55),
('Colorado', 9),
('Connecticut', 7),
('Delaware', 3),
('District de Columbia', 3),
('Floride', 29),
('Georgie', 16),
('Hawaii', 4),
('Idaho', 4),
('Illinois', 20),
('Indiana', 11),
('Iowa', 6),
('Kansas', 6),
('Kentucky', 8),
('Louisiane', 8),
('Maine', 4),
('Maryland', 10),
('Massachusetts', 11),
('Michigan', 16),
('Minnesota', 10),
('Mississippi', 6),
('Missouri', 10),
('Montana', 3),
('Nebraska', 5),
('Nevada', 6),
('New Hampshire', 4),
('New Jersey', 14),
('Nouveau Mexique', 5),
('New York', 29),
('Caroline du Nord', 15),
('Dakota du Nord', 3),
('Ohio', 18),
('Oklahoma', 7),
('Oregon', 7),
('Pennsylvanie', 20),
('Rhode Island', 4),
('Caroline du Sud', 9),
('Dakota du Sud', 3),
('Tennessee', 11),
('Texas', 38),
('Utah', 6),
('Vermont', 3),
('Virginie', 13),
('Washington', 12),
('Virginie Occidentale', 5),
('Wisconsin', 10),
('Wyoming', 3);

-- CANDIDATS
INSERT INTO candidats (nom, couleur) VALUES
('Joe Biden', 'blue'),
('Donald Trump', 'red');

-- ROLES ET UTILISATEURS
INSERT INTO roles (nom) VALUES
('admin'),
('observateur');

INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, role_id) VALUES
('admin', '$2y$10$ZfZGh.L35NJ7rNvkAf1h4OYk4h2hPDMa8Y5vFP1nYtHsfK2K3C9QC', 1), -- admin123
('user1', '$2y$10$T9B.xrtuAQKFGKU6p0nz.u353zBTuf8F68QVYT.1QYhe8pNfZlhMK', 2), -- user123
('user2', '$2y$10$T9B.xrtuAQKFGKU6p0nz.u353zBTuf8F68QVYT.1QYhe8pNfZlhMK', 2); -- user123

-- INITIALISATION DES VOTES (0 pour tous)
INSERT INTO votes (etat_id, candidat_id, nombre_voix)
SELECT e.id, c.id, 0
FROM etats e
CROSS JOIN candidats c;