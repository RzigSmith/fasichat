<?php
/**
 * FIX FINAL: Corriger le collation MySQL et le hash tronqué
 */

header('Content-Type: text/html; charset=utf-8');
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; } 
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
h1 { color: #333; border-bottom: 3px solid #28a745; padding-bottom: 10px; }
.ok { color: #28a745; font-weight: bold; }
.err { color: #dc3545; font-weight: bold; }
.info { background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin: 15px 0; }
code { background: #f9f9f9; padding: 5px; font-family: monospace; font-size: 12px; }
</style>";

echo "<div class='container'>";
echo "<h1>🔧 Correction Finale: Collation + Hash</h1>";

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();

    echo "<div class='info'>";
    echo "<strong>Étape 1:</strong> Changer le collation et augmenter la longueur de la colonne<br>";
    echo "</div>";

    // IMPORTANT: Augmenter la longueur et changer le collation
    $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN mot_de_passe VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    echo "<p><span class='ok'>✅ Colonne altérée</span></p>";

    echo "<div class='info'>";
    echo "<strong>Étape 2:</strong> Insérer le bon hash (60 caractères)<br>";
    echo "</div>";

    $correctHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Vérifier la longueur
    echo "Hash à insérer: <code>$correctHash</code> (" . strlen($correctHash) . " chars)<br><br>";

    // UPDATE avec prepared statement
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email IS NOT NULL");
    $stmt->execute([$correctHash]);
    
    echo "<p><span class='ok'>✅ " . $stmt->rowCount() . " utilisateurs mis à jour</span></p>";

    echo "<div class='info'>";
    echo "<strong>Étape 3:</strong> Vérifier le hash stocké<br>";
    echo "</div>";

    $user = $pdo->query("SELECT email, mot_de_passe, LENGTH(mot_de_passe) as len FROM utilisateurs WHERE email='apparitaire@fasichat.cd'")->fetch();
    
    if ($user) {
        echo "Email: <strong>" . $user['email'] . "</strong><br>";
        echo "Hash stocké: <code>" . $user['mot_de_passe'] . "</code><br>";
        echo "Longueur: <strong>" . $user['len'] . " caractères</strong><br><br>";
        
        $verified = password_verify('password123', $user['mot_de_passe']);
        echo "Test password_verify('password123'): <span style='font-weight: bold; color: " . ($verified ? '#28a745' : '#dc3545') . "'>" . ($verified ? '✅ VALIDE' : '❌ INVALIDE') . "</span><br>";
    }

    echo "<br><div class='info'>";
    echo "<strong>🎯 Essayez maintenant de vous connecter:</strong><br>";
    echo "Email: <code>apparitaire@fasichat.cd</code><br>";
    echo "Mot de passe: <code>password123</code><br><br>";
    echo "→ <a href='" . BASE_PATH . "/login' style='color: #0c5460; font-weight: bold; font-size: 16px;'>Aller à la connexion 🚀</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p><span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>
