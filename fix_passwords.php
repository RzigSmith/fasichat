<?php
/**
 * Script de correction des mots de passe corrompus
 * Les hashes bcrypt ont été mal stockés en base MySQL
 * Ce script les corrige avec les bons hashes
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
</style>";

echo "<div class='container'>";
echo "<h1>🔧 Correction des mots de passe</h1>";

// Charger les fichiers nécessaires
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();

    // Le hash correct de "password123"
    $correctHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    echo "<div class='info'>";
    echo "<strong>Hash correct pour 'password123':</strong><br>";
    echo "<code>$correctHash</code><br><br>";
    echo "Mise à jour de tous les mots de passe en base...";
    echo "</div>";

    // Exécuter DIRECTEMENT le SQL (pas de prepared statement pour éviter les problèmes d'encoding)
    $sql = "UPDATE utilisateurs SET mot_de_passe = '$correctHash' WHERE email IS NOT NULL";
    $pdo->exec($sql);

    $count = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    echo "<p><span class='ok'>✅ $count utilisateurs mis à jour!</span></p>";

    // Vérifier que ça fonctionne
    echo "<h2>Vérification après correction</h2>";
    $users = $pdo->query("SELECT id, email, mot_de_passe FROM utilisateurs LIMIT 3")->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Email</th><th>Hash</th><th>Vérification</th></tr>";
    foreach ($users as $user) {
        $verified = password_verify('password123', $user['mot_de_passe']) ? '✅ OK' : '❌ FAIL';
        echo "<tr>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td><code>" . substr($user['mot_de_passe'], 0, 30) . "...</code></td>";
        echo "<td>" . $verified . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div class='info'>";
    echo "<strong>✅ Correction terminée!</strong><br>";
    echo "Vous pouvez maintenant vous connecter avec:<br>";
    echo "<strong>Email:</strong> apparitaire@fasichat.cd<br>";
    echo "<strong>Mot de passe:</strong> password123<br>";
    echo "→ <a href='" . BASE_PATH . "/login'>Aller à la connexion</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p><span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>
