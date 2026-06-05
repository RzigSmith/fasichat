<?php
/**
 * DIAGNOSTIC: Teste la connexion MySQL et l'existence des données
 * Accès: http://localhost/fasichat/test_db_connection.php
 */

header('Content-Type: text/html; charset=utf-8');
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; } 
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
h2 { color: #555; margin-top: 30px; }
.ok { color: #28a745; font-weight: bold; }
.err { color: #dc3545; font-weight: bold; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-left: 4px solid #0c5460; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
table td, table th { padding: 10px; border: 1px solid #ddd; text-align: left; }
table th { background: #f9f9f9; font-weight: bold; }
code { background: #f9f9f9; padding: 2px 6px; border-radius: 3px; }
</style>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnostic de Connexion MySQL</h1>";

// Charge la configuration
require_once __DIR__ . '/config/config.php';

// Test 1: Paramètres de configuration
echo "<h2>1️⃣ Paramètres de configuration</h2>";
echo "DB_DRIVER: <strong>" . DB_DRIVER . "</strong><br>";
echo "DB_HOST: <strong>" . DB_HOST . "</strong><br>";
echo "DB_NAME: <strong>" . DB_NAME . "</strong><br>";
echo "DB_USER: <strong>" . DB_USER . "</strong><br>";
echo "DB_PASS: <strong>" . (DB_PASS ? '●●●●●●' : '(vide)') . "</strong><br><br>";

// Test 2: Tentative de connexion
echo "<h2>2️⃣ Test de connexion PDO</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "<span class='ok'>✅ Connexion réussie!</span><br><br>";

    // Test 3: Vérifier les tables
    echo "<h2>3️⃣ Tables existantes</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<span class='err'>❌ Aucune table trouvée! La base est vide.</span><br>";
    } else {
        echo "<span class='ok'>✅ Tables trouvées:</span><br>";
        foreach ($tables as $table) {
            echo "  - <strong>$table</strong><br>";
        }
    }
    echo "<br>";

    // Test 4: Vérifier les utilisateurs
    echo "<h2>4️⃣ Utilisateurs en base</h2>";
    $count = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    if ($count == 0) {
        echo "<span class='err'>❌ Aucun utilisateur! Les données n'ont pas été importées.</span><br>";
        echo "<strong>Solution:</strong> Exécutez le script <code>mysql_init.sql</code> dans phpMyAdmin<br>";
    } else {
        echo "<span class='ok'>✅ $count utilisateurs trouvés</span><br>";
        $users = $pdo->query("SELECT email, role FROM utilisateurs LIMIT 5")->fetchAll();
        foreach ($users as $user) {
            echo "  - <strong>{$user['email']}</strong> ({$user['role']})<br>";
        }
    }
    echo "<br>";

    // Test 5: Tester la vérification de mot de passe
    echo "<h2>5️⃣ Test de connexion avec apparitaire@fasichat.cd</h2>";
    $user = $pdo->query("SELECT * FROM utilisateurs WHERE email='apparitaire@fasichat.cd'")->fetch();
    if (!$user) {
        echo "<span class='err'>❌ Utilisateur non trouvé!</span><br>";
    } else {
        echo "<span class='ok'>✅ Utilisateur trouvé</span><br>";
        echo "<table>";
        echo "<tr><th>Champ</th><th>Valeur</th></tr>";
        echo "<tr><td>ID</td><td>" . $user['id'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $user['email'] . "</td></tr>";
        echo "<tr><td>Rôle</td><td>" . $user['role'] . "</td></tr>";
        echo "<tr><td>Hash stocké</td><td><code>" . substr($user['mot_de_passe'], 0, 20) . "...</code></td></tr>";
        echo "</table>";
        
        $hash = $user['mot_de_passe'];
        $test_password = 'password123';
        $verified = password_verify($test_password, $hash);
        
        echo "<h3>Test du mot de passe:</h3>";
        echo "Mot de passe testé: <code>$test_password</code><br>";
        echo "Hash en base: <code>$hash</code><br>";
        echo "Résultat password_verify(): " . ($verified ? "<span class='ok'>✅ VALIDE</span>" : "<span class='err'>❌ INVALIDE</span>") . "<br>";
    }
    echo "<br>";

    // Test 6: Tous les utilisateurs
    echo "<h2>6️⃣ Tous les utilisateurs en base</h2>";
    $all_users = $pdo->query("SELECT id, email, role, mot_de_passe FROM utilisateurs ORDER BY id")->fetchAll();
    if (empty($all_users)) {
        echo "<span class='err'>❌ Aucun utilisateur!</span><br>";
    } else {
        echo "<span class='ok'>✅ " . count($all_users) . " utilisateurs</span><br>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Email</th><th>Rôle</th><th>Hash</th></tr>";
        foreach ($all_users as $u) {
            echo "<tr>";
            echo "<td>" . $u['id'] . "</td>";
            echo "<td>" . $u['email'] . "</td>";
            echo "<td>" . $u['role'] . "</td>";
            echo "<td><code>" . substr($u['mot_de_passe'], 0, 30) . "...</code></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<br>";

    // Test 7: Résumé des actions
    echo "<h2>7️⃣ Résumé et actions</h2>";
    if (empty($all_users)) {
        echo "<div class='info'><strong>⚠️ PROBLÈME DÉTECTÉ:</strong> La base est vide!<br>";
        echo "<strong>Solution:</strong> Exécutez le script <code>mysql_init.sql</code><br>";
        echo "1. Ouvrez phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a><br>";
        echo "2. Allez dans l'onglet SQL<br>";
        echo "3. Ouvrez le fichier: <code>c:\\xampp\\htdocs\\fasichat\\database\\mysql_init.sql</code><br>";
        echo "4. Copiez-collez tout et cliquez Exécuter</div>";
    } else {
        echo "<div class='info'>✅ La base contient des données!<br>";
        echo "Essayez de vous connecter avec:<br>";
        echo "<strong>Email:</strong> apparitaire@fasichat.cd<br>";
        echo "<strong>Mot de passe:</strong> password123";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<span class='err'>❌ Erreur de connexion MySQL:</span><br>";
    echo "<strong>Code:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<div class='info'><strong>Vérifiez:</strong><br>";
    echo "1. MySQL est-il en cours d'exécution? (XAMPP Control Panel)<br>";
    echo "2. Les identifiants sont-ils corrects? (root / pas de mot de passe)<br>";
    echo "3. La base 'fasichat_classroom' existe-t-elle?<br>";
    echo "4. Vous pouvez aussi exécuter: <code>http://localhost/phpmyadmin</code>";
    echo "</div>";
}

echo "</div>";

?>
