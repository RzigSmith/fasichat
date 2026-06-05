-- ============================================================
-- FasiChat Classroom — Création complète de la base MySQL
-- Exécuter ce script dans phpMyAdmin
-- ============================================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS fasichat_classroom DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fasichat_classroom;

-- ============================================================
-- TABLE: promotions
-- ============================================================
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    annee VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'etudiant',
    promotion_id INT,
    avatar VARCHAR(255),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL,
    INDEX (email),
    INDEX (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: cours
-- ============================================================
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    promotion_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: cours_enseignants (Junction)
-- ============================================================
CREATE TABLE IF NOT EXISTS cours_enseignants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cours_id INT NOT NULL,
    enseignant_id INT NOT NULL,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY (cours_id, enseignant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: fichiers
-- ============================================================
CREATE TABLE IF NOT EXISTS fichiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_original VARCHAR(255) NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    type_mime VARCHAR(100),
    taille INT,
    auteur_id INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: messages
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    expediteur_id INT NOT NULL,
    destinataire_id INT,
    type VARCHAR(50) DEFAULT 'prive',
    promotion_id INT,
    fichier_id INT,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (fichier_id) REFERENCES fichiers(id) ON DELETE SET NULL,
    INDEX (date_envoi),
    INDEX (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: valve_annonces
-- ============================================================
CREATE TABLE IF NOT EXISTS valve_annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    auteur_id INT NOT NULL,
    date_expiration DATE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: convocations
-- ============================================================
CREATE TABLE IF NOT EXISTS convocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    objet VARCHAR(255) NOT NULL,
    date_reunion DATE NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    message TEXT,
    auteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    lue BOOLEAN DEFAULT FALSE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX (lue),
    INDEX (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: mur_pedagogique
-- ============================================================
CREATE TABLE IF NOT EXISTS mur_pedagogique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auteur_id INT NOT NULL,
    cours_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
    INDEX (date_publication)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Promotions
INSERT IGNORE INTO promotions (nom, annee) VALUES 
    ('L2 Informatique', '2025-2026'),
    ('L3 Informatique', '2025-2026');

-- Utilisateurs (mot de passe: password123)
-- Hash bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, promotion_id) VALUES
    ('Kamara',    'Oumar',    'doyen@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doyen',       NULL),
    ('Diallo',    'Fatoumata','vdoyen@fasichat.cd',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viceDoyen',   NULL),
    ('Koné',      'Ibrahim',  'apparitaire@fasichat.cd','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apparitaire', NULL),
    ('Traoré',    'Aminata',  'prof1@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant',  NULL),
    ('Bah',       'Seydou',   'prof2@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant',  NULL),
    ('Fofana',    'Marie',    'assistant@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'assistant',   NULL),
    ('Coulibaly', 'Jean',     'etudiant1@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1),
    ('Sylla',     'Mariam',   'etudiant2@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1),
    ('Touré',     'Ali',      'etudiant3@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1);

-- Cours
INSERT IGNORE INTO cours (titre, promotion_id) VALUES
    ('Programmation Web PHP',        1),
    ('Algorithmes & Structures',     1),
    ('Bases de Données',             1),
    ('Réseaux Informatiques',        2);

-- Affectation enseignants aux cours
INSERT IGNORE INTO cours_enseignants (cours_id, enseignant_id) VALUES
    (1, (SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd')),
    (2, (SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd')),
    (3, (SELECT id FROM utilisateurs WHERE email='prof2@fasichat.cd')),
    (1, (SELECT id FROM utilisateurs WHERE email='assistant@fasichat.cd'));

-- Annonces Valve
INSERT IGNORE INTO valve_annonces (titre, contenu, auteur_id) VALUES
    ('Bienvenue sur FasiChat Classroom',
     'Bienvenue sur la plateforme de messagerie académique FasiChat Classroom. Utilisez cette plateforme pour toutes vos communications pédagogiques.',
     (SELECT id FROM utilisateurs WHERE email='apparitaire@fasichat.cd')),
    ('Calendrier des examens',
     'Le calendrier des examens de fin de semestre est disponible au secrétariat. Les étudiants sont priés de vérifier leurs emplois du temps.',
     (SELECT id FROM utilisateurs WHERE email='apparitaire@fasichat.cd'));

-- Messages de démonstration
INSERT IGNORE INTO messages (contenu, expediteur_id, destinataire_id, type, promotion_id, date_envoi) VALUES
    ('Bonjour ! Bienvenue dans notre espace de messagerie.',
     (SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd'),
     (SELECT id FROM utilisateurs WHERE email='etudiant1@fasichat.cd'),
     'public',
     1,
     DATE_SUB(NOW(), INTERVAL 2 HOUR)),
    ('Merci Professeur ! Nous sommes ravis d''être ici.',
     (SELECT id FROM utilisateurs WHERE email='etudiant1@fasichat.cd'),
     (SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd'),
     'public',
     1,
     DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Publications sur le mur pédagogique
INSERT IGNORE INTO mur_pedagogique (auteur_id, cours_id, contenu, date_publication) VALUES
    ((SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd'),
     1,
     'Le TP PHP est disponible sur la plateforme. Vous avez jusqu''à la fin du mois pour le rendre. Bonne chance à tous !',
     DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
