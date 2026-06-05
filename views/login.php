<?php $pageTitle = 'Connexion — FasiChat Classroom'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <rect width="48" height="48" rx="14" fill="#4F46E5"/>
                <path d="M12 16h24M12 24h18M12 32h12" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            <h1>FasiChat <span>Classroom</span></h1>
            <p>Plateforme de messagerie académique</p>
        </div>

        <?php if ($erreur): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/login" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.cd"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>

        <div class="auth-demo">
            <p><strong>Comptes de démonstration</strong> (mot de passe : <code>password123</code>)</p>
            <div class="demo-accounts">
                <button onclick="fillLogin('doyen@fasichat.cd')">Doyen</button>
                <button onclick="fillLogin('vdoyen@fasichat.cd')">Vice-Doyen</button>
                <button onclick="fillLogin('apparitaire@fasichat.cd')">Apparitaire</button>
                <button onclick="fillLogin('prof1@fasichat.cd')">Enseignant</button>
                <button onclick="fillLogin('assistant@fasichat.cd')">Assistant</button>
                <button onclick="fillLogin('etudiant1@fasichat.cd')">Étudiant</button>
            </div>
        </div>
    </div>
</div>
<script>
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('mot_de_passe').value = 'password123';
}
</script>
</body>
</html>
