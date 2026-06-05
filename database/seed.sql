-- ============================================================
-- FasiChat Classroom — Données de démonstration
-- Mots de passe : tous "password123" (hash bcrypt inclus)
-- ============================================================

-- Promotions
INSERT IGNORE INTO promotions (nom, annee) VALUES ('L2 Informatique', '2025-2026');
INSERT IGNORE INTO promotions (nom, annee) VALUES ('L3 Informatique', '2025-2026');

-- Comptes de test (mot de passe : password123)
-- Hash bcrypt de "password123"
INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, promotion_id) VALUES
    ('Kamara',   'Oumar',    'doyen@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doyen',       NULL),
    ('Diallo',   'Fatoumata','vdoyen@fasichat.cd',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viceDoyen',   NULL),
    ('Koné',     'Ibrahim',  'apparitaire@fasichat.cd','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apparitaire', NULL),
    ('Traoré',   'Aminata',  'prof1@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant',  NULL),
    ('Bah',      'Seydou',   'prof2@fasichat.cd',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'enseignant',  NULL),
    ('Fofana',   'Marie',    'assistant@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'assistant',   NULL),
    ('Coulibaly', 'Jean',    'etudiant1@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1),
    ('Sylla',    'Mariam',   'etudiant2@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1),
    ('Touré',    'Ali',      'etudiant3@fasichat.cd',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant',    1);

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

-- Publication sur le mur pédagogique
INSERT IGNORE INTO mur_pedagogique (auteur_id, cours_id, contenu, date_publication) VALUES
    ((SELECT id FROM utilisateurs WHERE email='prof1@fasichat.cd'),
     1,
     'Le TP PHP est disponible sur la plateforme. Vous avez jusqu''à la fin du mois pour le rendre. Bonne chance à tous !',
     DATE_SUB(NOW(), INTERVAL 3 HOUR));
