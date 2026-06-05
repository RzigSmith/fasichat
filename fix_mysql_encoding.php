<?php
/**
 * Fix MySQL encoding - Corrige le collation de la colonne mot_de_passe
 * Le problème: utf8mb4_unicode_ci corrompt les hashes bcrypt
 * Solution: utiliser utf8mb4_bin ou VARBINARY
 */

header('Content-Type: text/html; charset=utf-8');
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; } 
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
h1 { color: #333; border-bottom: 3px solid #28a745; padding-bottom: 10px; }
.ok { color: #28a745; font-weight: bold; }
.err { color: #dc3545; font-weight: bold; }
.info { background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin: 15px 0; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
table td, table th { padding: 10px; border: 1px solid #ddd; text-align: left; }
table th { background: #f9f9f9; }
code { background: #f9f9f9; padding: 2px 6px; font-family: monospace; }
</style>";

echo "<div class='container'>";
echo "<h1>🔧 Configuration MySQL - Correction de l'encoding</h1>";

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();

    echo "<div class='info'>";
    echo "<strong>Étape 1:</strong> Vérifier la connexion MySQL<br>";
    echo "</div>";

    $test = $pdo->query("SELECT 1")->fetch();
    echo "<p><span class='ok'>✅ Connexion MySQL OK</span></p>";

    // Étape 2: Changer le collation de la colonne mot_de_passe
    echo "<div class='info'>";
    echo "<strong>Étape 2:</strong> Modifier la colonne mot_de_passe pour utiliser utf8mb4_bin<br>";
    echo "Cela préserve exactement les caractères spéciaux des hashes bcrypt";
    echo "</div>";

    $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN mot_de_passe VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL");
    echo "<p><span class='ok'>✅ Collation changée à utf8mb4_bin</span></p>";

    // Étape 3: Vider et remplir avec le bon hash
    echo "<div class='info'>";
    echo "<strong>Étape 3:</strong> Insérer le hash correct de 'password123'<br>";
    echo "</div>";

    $correctHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Utiliser prepared statement maintenant que le collation est correct
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email IS NOT NULL");
    $stmt->execute([$correctHash]);
    
    echo "<p><span class='ok'>✅ " . $stmt->rowCount() . " utilisateurs mis à jour</span></p>";

    echo "<h2>Vérification</h2>";
    $users = $pdo->query("SELECT id, email, mot_de_passe FROM utilisateurs LIMIT 3")->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Email</th><th>Hash (début)</th><th>Test password_verify()</th></tr>";
    foreach ($users as $user) {
        $verified = password_verify('password123', $user['mot_de_passe']) ? '<span class="ok">✅ OK</span>' : '<span class="err">❌ FAIL</span>';
        
        echo "<tr>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td><code>" . substr($user['mot_de_passe'], 0, 40) . "</code></td>";
        echo "<td>" . $verified . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div class='info'>";
    echo "<strong>✅ Configuration MySQL terminée!</strong><br><br>";
    echo "<strong>Testez la connexion avec:</strong><br>";
    echo "Email: <code>apparitaire@fasichat.cd</code><br>";
    echo "Mot de passe: <code>password123</code><br><br>";
    echo "→ <a href='" . BASE_PATH . "/login'>Aller à la connexion</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p><span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";
?>
