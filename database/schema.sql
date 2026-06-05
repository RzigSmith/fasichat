-- ============================================================
-- FasiChat Classroom — Schéma de la base de données SQLite
-- ============================================================

-- Table des promotions
CREATE TABLE IF NOT EXISTS promotions (
    id    INTEGER PRIMARY KEY AUTOINCREMENT,
    nom   TEXT    NOT NULL,
    annee TEXT    NOT NULL
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    nom              TEXT    NOT NULL,
    prenom           TEXT    NOT NULL,
    email            TEXT    NOT NULL UNIQUE,
    mot_de_passe     TEXT    NOT NULL,
    role             TEXT    NOT NULL CHECK(role IN ('etudiant','enseignant','assistant','doyen','viceDoyen','apparitaire')),
    promotion_id     INTEGER REFERENCES promotions(id) ON DELETE SET NULL,
    avatar           TEXT    DEFAULT '',
    date_inscription TEXT    DEFAULT (datetime('now'))
);

-- Table des cours
CREATE TABLE IF NOT EXISTS cours (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    titre        TEXT    NOT NULL,
    promotion_id INTEGER NOT NULL REFERENCES promotions(id) ON DELETE CASCADE
);

-- Table de liaison cours ↔ enseignants/assistants
CREATE TABLE IF NOT EXISTS cours_enseignants (
    cours_id       INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    enseignant_id  INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    PRIMARY KEY (cours_id, enseignant_id)
);

-- Table des fichiers
CREATE TABLE IF NOT EXISTS fichiers (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_original TEXT    NOT NULL,
    nom_stocke   TEXT    NOT NULL,
    type_mime    TEXT    NOT NULL,
    taille       INTEGER NOT NULL,
    chemin       TEXT    NOT NULL,
    uploadeur_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    date_upload  TEXT    DEFAULT (datetime('now'))
);

-- Table des messages (privés et publics)
CREATE TABLE IF NOT EXISTS messages (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    contenu         TEXT,
    expediteur_id   INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    destinataire_id INTEGER REFERENCES utilisateurs(id) ON DELETE CASCADE,
    type            TEXT    NOT NULL CHECK(type IN ('prive','public','mur')),
    promotion_id    INTEGER REFERENCES promotions(id) ON DELETE CASCADE,
    fichier_id      INTEGER REFERENCES fichiers(id) ON DELETE SET NULL,
    date_envoi      TEXT    DEFAULT (datetime('now'))
);

-- Table des convocations
CREATE TABLE IF NOT EXISTS convocations (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    objet         TEXT    NOT NULL,
    date_reunion  TEXT    NOT NULL,
    lieu          TEXT    NOT NULL,
    message       TEXT    DEFAULT '',
    expediteur_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    date_envoi    TEXT    DEFAULT (datetime('now'))
);

-- Table de liaison convocation ↔ destinataires
CREATE TABLE IF NOT EXISTS convocation_destinataires (
    convocation_id  INTEGER NOT NULL REFERENCES convocations(id) ON DELETE CASCADE,
    destinataire_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    lu              INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (convocation_id, destinataire_id)
);

-- Table du Valve (annonces institutionnelles)
CREATE TABLE IF NOT EXISTS valve_annonces (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    titre            TEXT    NOT NULL,
    contenu          TEXT    NOT NULL,
    auteur_id        INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    date_publication TEXT    DEFAULT (datetime('now')),
    date_expiration  TEXT    DEFAULT NULL
);

-- Table du Mur Pédagogique
CREATE TABLE IF NOT EXISTS mur_pedagogique (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    auteur_id        INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    cours_id         INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    contenu          TEXT    NOT NULL,
    date_publication TEXT    DEFAULT (datetime('now'))
);

-- Index pour les performances
CREATE INDEX IF NOT EXISTS idx_messages_expediteur   ON messages(expediteur_id);
CREATE INDEX IF NOT EXISTS idx_messages_destinataire ON messages(destinataire_id);
CREATE INDEX IF NOT EXISTS idx_messages_promotion    ON messages(promotion_id);
CREATE INDEX IF NOT EXISTS idx_convdest_dest         ON convocation_destinataires(destinataire_id);
CREATE INDEX IF NOT EXISTS idx_utilisateurs_role     ON utilisateurs(role);
CREATE INDEX IF NOT EXISTS idx_mur_cours             ON mur_pedagogique(cours_id);

-- Converted to MySQL-compatible schema (InnoDB, AUTO_INCREMENT, CURRENT_TIMESTAMP)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `convocation_destinataires`, `convocations`, `messages`, `mur_pedagogique`, `valve_annonces`, `fichiers`, `cours_enseignants`, `cours`, `utilisateurs`, `promotions`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS `promotions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `annee` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `utilisateurs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `role` ENUM('etudiant','enseignant','assistant','doyen','viceDoyen','apparitaire') NOT NULL,
    `promotion_id` INT DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT '',
    `date_inscription` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cours` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `promotion_id` INT NOT NULL,
    FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cours_enseignants` (
    `cours_id` INT NOT NULL,
    `enseignant_id` INT NOT NULL,
    PRIMARY KEY (`cours_id`,`enseignant_id`),
    FOREIGN KEY (`cours_id`) REFERENCES `cours`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fichiers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom_original` VARCHAR(255) NOT NULL,
    `nom_stocke` VARCHAR(255) NOT NULL,
    `type_mime` VARCHAR(127) NOT NULL,
    `taille` INT NOT NULL,
    `chemin` VARCHAR(1024) NOT NULL,
    `uploadeur_id` INT NOT NULL,
    `date_upload` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`uploadeur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contenu` TEXT,
    `expediteur_id` INT NOT NULL,
    `destinataire_id` INT DEFAULT NULL,
    `type` ENUM('prive','public','mur') NOT NULL,
    `promotion_id` INT DEFAULT NULL,
    `fichier_id` INT DEFAULT NULL,
    `date_envoi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fichier_id`) REFERENCES `fichiers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `convocations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `objet` VARCHAR(255) NOT NULL,
    `date_reunion` DATETIME NOT NULL,
    `lieu` VARCHAR(255) NOT NULL,
    `message` TEXT DEFAULT '',
    `expediteur_id` INT NOT NULL,
    `date_envoi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `convocation_destinataires` (
    `convocation_id` INT NOT NULL,
    `destinataire_id` INT NOT NULL,
    `lu` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`convocation_id`,`destinataire_id`),
    FOREIGN KEY (`convocation_id`) REFERENCES `convocations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `valve_annonces` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `contenu` TEXT NOT NULL,
    `auteur_id` INT NOT NULL,
    `date_publication` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_expiration` DATETIME DEFAULT NULL,
    FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mur_pedagogique` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `auteur_id` INT NOT NULL,
    `cours_id` INT NOT NULL,
    `contenu` TEXT NOT NULL,
    `date_publication` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cours_id`) REFERENCES `cours`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX `idx_messages_expediteur` ON `messages`(`expediteur_id`);
CREATE INDEX `idx_messages_destinataire` ON `messages`(`destinataire_id`);
CREATE INDEX `idx_messages_promotion` ON `messages`(`promotion_id`);
CREATE INDEX `idx_convdest_dest` ON `convocation_destinataires`(`destinataire_id`);
CREATE INDEX `idx_utilisateurs_role` ON `utilisateurs`(`role`);
CREATE INDEX `idx_mur_cours` ON `mur_pedagogique`(`cours_id`);
