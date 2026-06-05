<?php
/**
 * SOLUTION DÉFINITIVE: Générer un nouveau hash avec password_hash()
 * au lieu d'utiliser le hash pré-calculé
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
echo "<h1>🔥 Solution Définitive: Générer un nouveau hash</h1>";

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = BaseDeDonnees::getInstance();
    $pdo = $db->getPDO();

    echo "<div class='info'>";
    echo "<strong>Stratégie:</strong> Au lieu de copier un hash pré-calculé,<br>";
    echo "générer un NOUVEAU hash de 'password123' avec password_hash() directement en MySQL<br>";
    echo "</div>";

    // Générer un hash FRAIS de 'password123'
    $freshHash = password_hash('password123', PASSWORD_BCRYPT);
    
    echo "<p>Hash généré localement: <code>$freshHash</code></p>";
    echo "<p>Longueur: " . strlen($freshHash) . " caractères</p>";

    // Vérifier que ce hash fonctionne localement
    $localTest = password_verify('password123', $freshHash);
    echo "<p>Test local: " . ($localTest ? '<span class="ok">✅ OK</span>' : '<span class="err">❌ FAIL</span>') . "</p><br>";

    echo "<div class='info'>";
    echo "<strong>Étape 1:</strong> Changer le collation en utf8mb4_bin (préserve les bytes exactement)<br>";
    echo "</div>";

    $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN mot_de_passe VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL");
    echo "<p><span class='ok'>✅ Collation changée à utf8mb4_bin</span></p>";

    echo "<div class='info'>";
    echo "<strong>Étape 2:</strong> Exécuter le SQL RAW pour insérer le hash<br>";
    echo "(pas de prepared statement pour éviter les pièges d'encoding)<br>";
    echo "</div>";

    // Utiliser SQL RAW avec le hash échappé
    $escapedHash = $pdo->quote($freshHash);
    $sql = "UPDATE utilisateurs SET mot_de_passe = $escapedHash WHERE 1=1";
    
    $result = $pdo->exec($sql);
    echo "<p><span class='ok'>✅ $result utilisateurs mis à jour</span></p>";

    echo "<div class='info'>";
    echo "<strong>Étape 3:</strong> Vérifier que ça fonctionne<br>";
    echo "</div>";

    $user = $pdo->query("SELECT email, mot_de_passe FROM utilisateurs WHERE email='apparitaire@fasichat.cd'")->fetch();
    
    if ($user) {
        echo "Email: <strong>" . $user['email'] . "</strong><br>";
        echo "Hash en base: <code>" . $user['mot_de_passe'] . "</code><br>";
        
        $verified = password_verify('password123', $user['mot_de_passe']);
        echo "password_verify('password123'): <span style='font-weight: bold; color: " . ($verified ? '#28a745' : '#dc3545') . "'>" . ($verified ? '✅ VALIDE!' : '❌ INVALIDE') . "</span><br>";
        
        if (!$verified) {
            echo "<br><strong>⚠️ Debug info:</strong><br>";
            echo "Hash récupéré: <code>" . substr($user['mot_de_passe'], 0, 60) . "</code><br>";
            echo "Longueur: " . strlen($user['mot_de_passe']) . " chars<br>";
        }
    }

    echo "<br><div class='info'>";
    echo "<strong>🎯 Testez la connexion:</strong><br>";
    echo "Email: <code>apparitaire@fasichat.cd</code><br>";
    echo "Mot de passe: <code>password123</code><br><br>";
    echo "→ <a href='" . BASE_PATH . "/login' style='color: #0c5460; font-weight: bold; font-size: 16px;'>Aller à la connexion 🚀</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p><span class='err'>❌ Erreur:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";
?>
