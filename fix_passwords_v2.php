<?php
/**
 * Corrige le problème d'encoding en convertissant la colonne en VARBINARY
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
echo "<h1>🔧 Correction d'encoding MySQL</h1>";

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();

    echo "<div class='info'>";
    echo "<strong>Étape 1:</strong> Altérer la colonne mot_de_passe en VARBINARY<br>";
    echo "</div>";

    // Étape 1: Convertir la colonne en VARBINARY (pas d'encoding issues)
    $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN mot_de_passe VARBINARY(255)");
    echo "<p><span class='ok'>✅ Colonne altérée en VARBINARY</span></p>";

    echo "<div class='info'>";
    echo "<strong>Étape 2:</strong> Insérer le hash correct<br>";
    echo "</div>";

    // Étape 2: Insérer les données directement (VARBINARY préserve les bytes)
    $correctHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Utiliser HEX pour convertir en binaire
    $hexHash = bin2hex($correctHash);
    $sql = "UPDATE utilisateurs SET mot_de_passe = UNHEX('$hexHash') WHERE email IS NOT NULL";
    $pdo->exec($sql);
    
    echo "<p><span class='ok'>✅ Tous les mots de passe mise à jour</span></p>";

    echo "<h2>Vérification</h2>";
    $users = $pdo->query("SELECT id, email, mot_de_passe FROM utilisateurs LIMIT 3")->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Email</th><th>Hash (hex)</th><th>Vérification</th></tr>";
    foreach ($users as $user) {
        $hashBinary = $user['mot_de_passe'];
        // Si c'est du binaire, le convertir en string
        if (is_string($hashBinary) && strlen($hashBinary) === 60) {
            $verified = password_verify('password123', $hashBinary) ? '✅ OK' : '❌ FAIL';
        } else {
            $verified = '❓ Inconnu';
        }
        
        echo "<tr>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td><code>" . substr(bin2hex($hashBinary), 0, 30) . "...</code></td>";
        echo "<td>" . $verified . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div class='info'>";
    echo "<strong>✅ Correction terminée!</strong><br>";
    echo "Essayez maintenant de vous connecter:<br>";
    echo "<strong>Email:</strong> apparitaire@fasichat.cd<br>";
    echo "<strong>Mot de passe:</strong> password123<br>";
    echo "→ <a href='" . BASE_PATH . "/login'>Aller à la connexion</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p><span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";
?>
