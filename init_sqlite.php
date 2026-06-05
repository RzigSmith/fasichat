<?php
/**
 * Script d'initialisation de la base de données SQLite
 * Crée la base de données fasichat.db et initialise le schéma
 */

// Charger la configuration
require_once __DIR__ . '/config/config.php';

try {
    // Créer la connexion SQLite
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lire et exécuter le schéma
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($schema);
    
    echo "✓ Base de données SQLite initialisée avec succès!\n";
    echo "Fichier: " . DB_PATH . "\n";
    
    // Optionnel: insérer des données de test
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    if ($seed) {
        $pdo->exec($seed);
        echo "✓ Données de test insérées\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
