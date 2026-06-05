<?php
/**
 * TEST COMPLET: Connexion MySQL + Authentification
 */

header('Content-Type: text/html; charset=utf-8');
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; } 
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
h1 { color: #333; border-bottom: 3px solid #28a745; padding-bottom: 10px; }
h2 { color: #555; margin-top: 30px; }
.ok { color: #28a745; font-weight: bold; }
.err { color: #dc3545; font-weight: bold; }
.info { background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin: 15px 0; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
table td, table th { padding: 10px; border: 1px solid #ddd; text-align: left; }
table th { background: #f9f9f9; }
code { background: #f9f9f9; padding: 2px 6px; font-family: monospace; }
</style>";

echo "<div class='container'>";
echo "<h1>✅ Test Complet: MySQL + Authentification</h1>";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// Test 1: Configuration
echo "<h2>1️⃣ Configuration</h2>";
echo "DB_DRIVER: <strong>" . DB_DRIVER . "</strong><br>";
echo "DB_HOST: <strong>" . DB_HOST . "</strong><br>";
echo "DB_NAME: <strong>" . DB_NAME . "</strong><br><br>";

// Test 2: Connexion MySQL
echo "<h2>2️⃣ Connexion MySQL</h2>";
try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();
    
    $test = $pdo->query("SELECT 1")->fetch();
    echo "<span class='ok'>✅ Connexion réussie!</span><br><br>";
    
    // Test 3: Vérifier les utilisateurs
    echo "<h2>3️⃣ Utilisateurs en base</h2>";
    $users = $pdo->query("SELECT id, email, role FROM utilisateurs")->fetchAll();
    echo "<span class='ok'>✅ " . count($users) . " utilisateurs trouvés</span><br><br>";
    
    echo "<table>";
    echo "<tr><th>Email</th><th>Rôle</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>" . $user['email'] . "</td><td>" . $user['role'] . "</td></tr>";
    }
    echo "</table>";
    
    // Test 4: Tester la vérification de mot de passe
    echo "<h2>4️⃣ Test d'authentification</h2>";
    $user = $pdo->query("SELECT * FROM utilisateurs WHERE email='apparitaire@fasichat.cd'")->fetch();
    
    if ($user) {
        echo "Utilisateur trouvé: <strong>" . $user['email'] . "</strong><br>";
        echo "Hash stocké: <code>" . substr($user['mot_de_passe'], 0, 50) . "</code><br>";
        
        $test_password = 'password123';
        $verified = password_verify($test_password, $user['mot_de_passe']);
        
        if ($verified) {
            echo "Mot de passe testé: <code>$test_password</code><br>";
            echo "Résultat: <span class='ok'>✅ VALIDE - La connexion devrait fonctionner!</span><br>";
        } else {
            echo "Mot de passe testé: <code>$test_password</code><br>";
            echo "Résultat: <span class='err'>❌ INVALIDE</span><br>";
        }
    }
    
    echo "<br><div class='info'>";
    echo "<strong>🎯 Essayez de vous connecter:</strong><br>";
    echo "Email: <code>apparitaire@fasichat.cd</code><br>";
    echo "Mot de passe: <code>password123</code><br><br>";
    echo "→ <a href='" . BASE_PATH . "/login' style='color: #0c5460; font-weight: bold;'>Aller à la connexion</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "</div>";
?>
